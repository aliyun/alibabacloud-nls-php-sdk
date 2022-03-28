<?php

namespace SpeechRecognizerDemo;

use AlibabaCloud\NLS\Token;
use AlibabaCloud\NLS\Recognizer;

class SpeechRecognizerDemo {
    public static function onRecognitionStarted($event) {
        printf("[onRecognitionStarted] task id: %s\n", $event->_taskId);
        printf("[onRecognitionStarted] status code: %s\n", $event->_statusCode);
        printf("[onRecognitionStarted] all message: %s\n", $event->_msg);
    }
    public static function onTaskFailed($event) {
        printf("[onTaskFailed] task id: %s\n", $event->_taskId);
        printf("[onTaskFailed] status code: %s\n", $event->_statusCode);
        printf("[onTaskFailed] all message: %s\n", $event->_msg);
    }
    public static function onRecognitionCompleted($event) {
        printf("[onRecognitionCompleted] task id: %s\n", $event->_taskId);
        printf("[onRecognitionCompleted] status code: %s\n", $event->_statusCode);
        printf("[onRecognitionCompleted] result: %s\n", $event->_result);
        printf("[onRecognitionCompleted] all message: %s\n", $event->_msg);
    }
    public static function onRecognitionResultChanged($event) {
        printf("[onRecognitionResultChanged] task id: %s\n", $event->_taskId);
        printf("[onRecognitionResultChanged] status code: %s\n", $event->_statusCode);
        printf("[onRecognitionResultChanged] result: %s\n", $event->_result);
        printf("[onRecognitionResultChanged] all message: %s\n", $event->_msg);
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
        if (empty($args[4])) {
            die("Please input audio file.\n");
        }

        $akId = $args[0];
        $akSecret = $args[1];
        $appKey = $args[2];
        $port = $args[3];
        $filePath = $args[4];
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
        printf("token expire time: %d\n", $expireTime);

        /**
         * 2. 创建一个一句话识别的本地客户端, 用于与实时识别本地服务端进行通信. 
         */
        // 创建一句话识别客户端
        $recognizer = new Recognizer\SpeechRecognizerClient($port);
        // 传入事件回调
        $recognizer->setOnRecognitionStarted('onRecognitionStarted', 'UserId');
        $recognizer->setOnTaskFailed('onTaskFailed', 'UserId');
        $recognizer->setOnRecognitionCompleted('onRecognitionCompleted', 'UserId');
        $recognizer->setOnRecognitionResultChanged('onRecognitionResultChanged', 'UserId');

        // 设置一句话识别参数
        $recognizer->setNamespace(__NAMESPACE__, 'SpeechRecognizerDemo');  // 用于回调, 传入回调函数所在命名空间名和类名
        //$recognizer->setUrl("wss://nls-gateway-ap-southeast-1.aliyuncs.com/ws/v1"); // 若需要调用国际站服务
        $recognizer->setAppKey($appKey);          // 传入项目appkey
        $recognizer->setToken($curToken);         // 传入有效访问令牌token
        $recognizer->setIntermediateResult(true); // 开启识别中间结果, 默认关闭

        /**
         * 3. 一句话识别的本地客户端启动一轮请求
         */
        $arrayResult = $recognizer->start();
        if (is_array($arrayResult)) {
            var_dump($arrayResult);

        } else {
            printf("[speechRecognizerDemo] start failed.\n");
        }

        /**
         * 4. 以从音频文件取出音频数据来模拟真实录音场景.
         *    如下为例, 每100ms传入3200字节音频数据, 同时sendAudio返回事件结果
         */
        if (file_exists($filePath)) {
            $fp = fopen($filePath, "r");
            $bufferSize = 3200;  // 每次读取3200字节,相当于读100ms单通道16K音频
            while (!feof($fp)) { // 循环读取，直至读取完整个文件
                $buffer = fread($fp, $bufferSize);
                $getSize = strlen($buffer);
                // sendAudio传入音频数据, 返回事件array用于解析. 若无事件收到, 则返回false
                // 用户除了通过解析sendAudio()返回的array解析结果,
                // 也可以通过回调函数得到的NlsEvent来解析
                $sendResult = $recognizer->sendAudio($buffer, $getSize);
                if (is_array($sendResult)) {
                    var_dump($sendResult);
                } else {
                    if (is_bool($sendResult) && $sendResult === false) {
                        printf("[speechRecognizerDemo] sendAudio failed.\n");
                    }
                }
                usleep(100 * 1000);
            }
            fclose($fp);
        }

        /**
         * 5. 若想结束此次一句话识别, 调用stop, 并解析返回事件array. 若无array返回则说明stop调用失败.
         */
        $stopResult = $recognizer->stop();
        if (is_array($stopResult)) {
            var_dump($stopResult);
        } else {
            printf("[speechRecognizerDemo] stop failed.\n");
        }

        /**
         * 6. 释放此次一句话识别的本地客户端
         */
        unset($recognizer);
        printf("[speechRecognizerDemo] finish.\n");
    }

    public static function register() {
        $file = dirname(__FILE__) . \DIRECTORY_SEPARATOR . '..' . '/nls/token/autoload.php';
        require_once $file;
        $file = dirname(__FILE__) . \DIRECTORY_SEPARATOR . '..' . '/nls/common/autoload.php';
        require_once $file;
        $file = dirname(__FILE__) . \DIRECTORY_SEPARATOR . '..' . '/nls/transport/websocket/autoload.php';
        require_once $file;
        $file = dirname(__FILE__) . \DIRECTORY_SEPARATOR . '..' . '/nls/recognizer/autoload.php';
        require_once $file;
    }
}

require_once dirname(__FILE__) . \DIRECTORY_SEPARATOR . '..' . \DIRECTORY_SEPARATOR . '/vendor/autoload.php';
SpeechRecognizerDemo::main(array_slice($argv, 1));

?>
