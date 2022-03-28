<?php

namespace SpeechSynthesizerDemo;

use AlibabaCloud\NLS\Token;
use AlibabaCloud\NLS\Synthesizer;

class SpeechSynthesizerDemo {
    protected static $_running = false;
    protected static $_fileFormate;

    public static function onTaskFailed($event) {
        printf("[onTaskFailed] task id: %s\n", $event->_taskId);
        printf("[onTaskFailed] status code: %s\n", $event->_statusCode);
        printf("[onTaskFailed] all message: %s\n", $event->_msg);
        self::$_running = false;
    }
    public static function onSynthesisCompleted($event) {
        printf("[onSynthesisCompleted] task id: %s\n", $event->_taskId);
        printf("[onSynthesisCompleted] status code: %s\n", $event->_statusCode);
        printf("[onSynthesisCompleted] all message: %s\n", $event->_msg);
        self::$_running = false;
    }
    public static function onBinaryDataReceived($event) {
        printf("[onBinaryDataReceived] task id: %s\n", $event->_taskId);
        printf("[onBinaryDataReceived] data size: %s\n", $event->_dataSize);

        /**
         * 将每轮语音合成(即每个task)音频进行本地存储, 以模拟和验证语音合成功能
         */
        $filePath = $event->_taskId . '.' . self::$_fileFormate;
        if ($event->_dataSize > 0) {
            $fp = fopen($filePath, "a");
            printf("[onBinaryDataReceived] fwrite audio data(%dbytes) to %s\n",
                $event->_dataSize, $filePath);
            $result = fwrite($fp, $event->_data, $event->_dataSize);
            fclose($fp);
        }
    }
    public function onMetaInfo($event) {
        printf("[onMetaInfo] task id: %s\n", $event->_taskId);
        printf("[onMetaInfo] status code: %s\n", $event->_statusCode);
        printf("[onMetaInfo] all message: %s\n", $event->_msg);
    }

    /**
     * @param string[] $args
     * @return void
     */
    public static function main($args) {
        self::register();

        if (empty($args[0])) {
            die("Please input akId.\n");
        }
        if (empty($args[1])) {
            die("Please input akSecret.\n");
        }
        if (empty($args[2])) {
            die("Please input appkey.\n");
        }
        if (empty($args[3])) {
            die("Please input service port.\n");
        }

        $akId = $args[0];
        $akSecret = $args[1];
        $appKey = $args[2];
        $port = $args[3];
        printf("ak id: %s\n", $akId);
        printf("appKey: %s\n", $appKey);

        $expireTime = 0;
        $curToken = 0;

        /**
         * 1. 判断token的超时时间戳, 若超过则需要重新申请token, 否则继续使用原token进行语音交互.
         */
        $curTime = strtotime("now"); // 注意时区
        printf("current timestamp: %d\n", $curTime);
        if ($curTime >= $expireTime) {
            $tokenClient = new Token\NlsToken();
            $tokenClient->setAccessKeyId($akId);
            $tokenClient->setKeySecret($akSecret);
            // 若为国际站语音服务, 则需要使用国际站访问域名
            //$tokenClient->setDomain("nlsmeta.ap-southeast-1.aliyuncs.com");
            $tokenClient->applyNlsToken();

            $curToken = $tokenClient->getToken();
            $expireTime = $tokenClient->getExpireTime();
        }
        printf("token id: %s\n", $curToken);
        printf("token expire timestamp: %d\n", $expireTime);
    
        /**
         * 2. 创建一个语音合成的本地客户端, 用于与语音合成本地服务端进行通信. 
         */
        // 创建语音合成客户端
        $synthesizer = new Synthesizer\SpeechSynthesizerClient($port);
        // 传入事件回调
        $synthesizer->setOnTaskFailed('onTaskFailed', 'UserId');
        $synthesizer->setOnSynthesisCompleted('onSynthesisCompleted', 'UserId');
        $synthesizer->setOnBinaryDataReceived('onBinaryDataReceived', 'UserId');
        $synthesizer->setOnMetaInfo('onMetaInfo', 'UserId');

        // 设置语音合成参数
        $synthesizer->setNamespace(__NAMESPACE__, 'SpeechSynthesizerDemo');  // 用于回调, 传入回调函数所在命名空间名和类名
        //$synthesizer->setUrl("wss://nls-gateway-ap-southeast-1.aliyuncs.com/ws/v1"); // 若需要调用国际站服务
        $synthesizer->setAppKey($appKey);   // 传入项目appkey
        $synthesizer->setToken($curToken);  // 传入有效访问令牌token
        // 发音人, 包含"xiaoyun", "ruoxi", "xiaogang"等. 可选参数, 默认是xiaoyun
        $synthesizer->setVoice("siqi");
        // 音量, 范围是0~100, 可选参数, 默认50
        $synthesizer->setVolume(50);
        // 音频编码格式, 可选参数, 默认是wav. 支持的格式pcm, wav, mp3
        self::$_fileFormate = 'wav';
        $synthesizer->setFormat(self::$_fileFormate);
        // 音频采样率, 包含8000, 16000. 可选参数, 默认是16000
        $synthesizer->setSampleRate(16000);
        // 语速, 范围是-500~500, 可选参数, 默认是0
        $synthesizer->setSpeechRate(0);
        // 语调, 范围是-500~500, 可选参数, 默认是0
        $synthesizer->setPitchRate(0);
        // 开启字幕
        $synthesizer->setEnableSubtitle(true);
        // 设置待合成文本, 必填参数. 文本内容必须为UTF-8编码
        $text = '今天天气真不错，我想要去踢足球。'; // UTF8
        $synthesizer->setText($text);
        

        self::$_running = true;
        /**
         * 3. 语音合成的本地客户端启动一轮请求
         */
        $startResult = $synthesizer->start();
        if ($startResult === false) {
            printf("[SpeechSynthesizerDemo] start failed.\n");
        }

        /**
         * 4. 以不停调用getEvent()获取语音合成的结果, 直到结束或发现错误
         */
        while (self::$_running === true) {
            // 用户除了通过解析getEvent()返回的array解析结果,
            // 也可以通过回调函数得到的NlsEvent来解析
            $getResult = $synthesizer->getEvent();
            if (is_array($getResult)) {
                var_dump($getResult);
            } else {
                if (is_bool($getResult) && $getResult === false) {
                    printf("[SpeechSynthesizerDemo] getEvent failed.\n");
                }
            }

            // 语音合成的机制为通过轮询调用getEvent()来推动获取语音合成的数据.
            // 由于语音合成会在数百毫秒内完成, 所以轮询间隔建议不要太大
            usleep(10 * 1000);
        }

        /**
         * 5. 释放此次语音合成的本地客户端
         */
        unset($synthesizer);
        printf("[SpeechSynthesizerDemo] finish.\n");
    }

    public static function register() {
        $file = dirname(__FILE__) . \DIRECTORY_SEPARATOR . '..' . '/nls/token/autoload.php';
        require_once $file;
        $file = dirname(__FILE__) . \DIRECTORY_SEPARATOR . '..' . '/nls/common/autoload.php';
        require_once $file;
        $file = dirname(__FILE__) . \DIRECTORY_SEPARATOR . '..' . '/nls/transport/websocket/autoload.php';
        require_once $file;
        $file = dirname(__FILE__) . \DIRECTORY_SEPARATOR . '..' . '/nls/synthesizer/autoload.php';
        require_once $file;
    }
}

require_once dirname(__FILE__) . \DIRECTORY_SEPARATOR . '..' . \DIRECTORY_SEPARATOR . '/vendor/autoload.php';
SpeechSynthesizerDemo::main(array_slice($argv, 1));

?>
