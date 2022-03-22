<?php

namespace SpeechTranscriberDemo;

use AlibabaCloud\NLS\Token;
use AlibabaCloud\NLS\Transcriber;

class SpeechTranscriberDemo {
    public static function onTranscriptionStarted($event) {
        printf("[onTranscriptionStarted] task id: %s\n", $event->_taskId);
        printf("[onTranscriptionStarted] status code: %s\n", $event->_statusCode);
        printf("[onTranscriptionStarted] all message: %s\n", $event->_msg);
    }
    public static function onTaskFailed($event) {
        printf("[onTaskFailed] task id: %s\n", $event->_taskId);
        printf("[onTaskFailed] status code: %s\n", $event->_statusCode);
        printf("[onTaskFailed] all message: %s\n", $event->_msg);
    }
    public static function onTranscriptionCompleted($event) {
        printf("[onTranscriptionCompleted] task id: %s\n", $event->_taskId);
        printf("[onTranscriptionCompleted] status code: %s\n", $event->_statusCode);
        printf("[onTranscriptionCompleted] all message: %s\n", $event->_msg);
    }
    public static function onTranscriptionResultChanged($event) {
        printf("[onTranscriptionResultChanged] task id: %s\n", $event->_taskId);
        printf("[onTranscriptionResultChanged] status code: %s\n", $event->_statusCode);
        printf("[onTranscriptionResultChanged] result: %s\n", $event->_result);
        printf("[onTranscriptionResultChanged] all message: %s\n", $event->_msg);
    }

    public static function onSentenceBegin($event)
    {
        printf("[onSentenceBegin] task id: %s\n", $event->_taskId);
        printf("[onSentenceBegin] status code: %s\n", $event->_statusCode);
        printf("[onSentenceBegin] all message: %s\n", $event->_msg);
    }
    public static function onSentenceEnd($event)
    {
        printf("[onSentenceEnd] task id: %s\n", $event->_taskId);
        printf("[onSentenceEnd] status code: %s\n", $event->_statusCode);
        printf("[onSentenceEnd] result: %s\n", $event->_result);
        printf("[onSentenceEnd] all message: %s\n", $event->_msg);
    }
    public static function onSentenceSemantics($event)
    {
        printf("[onSentenceSemantics] task id: %s\n", $event->_taskId);
        printf("[onSentenceSemantics] status code: %s\n", $event->_statusCode);
        printf("[onSentenceSemantics] all message: %s\n", $event->_msg);
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
        printf("token expire timestamp: %d\n", $expireTime);

        /**
         * 2. 创建一个实时识别的本地客户端, 用于与实时识别本地服务端进行通信. 
         */
        // 创建实时识别客户端
        $transcriber = new Transcriber\SpeechTranscriberClient($port);
        // 传入事件回调
        $transcriber->setOnTranscriptionStarted('onTranscriptionStarted', 'UserId');
        $transcriber->setOnTaskFailed('onTaskFailed', 'UserId');
        $transcriber->setOnTranscriptionCompleted('onTranscriptionCompleted', 'UserId');
        $transcriber->setOnTranscriptionResultChanged('onTranscriptionResultChanged', 'UserId');
        $transcriber->setOnSentenceSemantics('onSentenceSemantics', 'UserId');
        $transcriber->setOnSentenceBegin('onSentenceBegin', 'UserId');
        $transcriber->setOnSentenceEnd('onSentenceEnd', 'UserId');

        // 设置实时识别参数
        $transcriber->setNamespace(__NAMESPACE__, 'SpeechTranscriberDemo');  // 用于回调, 传入回调函数所在命名空间名和类名
        //$transcriber->setUrl("wss://nls-gateway-ap-southeast-1.aliyuncs.com/ws/v1"); // 若需要调用国际站服务
        $transcriber->setAppKey($appKey);          // 传入项目appkey
        $transcriber->setToken($curToken);         // 传入有效访问令牌token
        $transcriber->setIntermediateResult(true); // 开启识别中间结果, 默认关闭

        /**
         * 3. 实时识别的本地客户端启动一轮请求
         */
        $startResult = $transcriber->start();
        if (is_array($startResult)) {
            var_dump($startResult);
        } else {
            printf("[speechTranscriberDemo] start failed.\n");
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
                $sendResult = $transcriber->sendAudio($buffer, $getSize);
                if (is_array($sendResult)) {
                    var_dump($sendResult);
                } else {
                    if (is_bool($sendResult) && $sendResult === false) {
                        printf("[speechTranscriberDemo] sendAudio failed.\n");
                    }
                }
                usleep(100 * 1000);
            }
            fclose($fp);
        }

        /**
         * 5. 若想结束此次实时识别, 调用stop, 并解析返回事件array. 若无array返回则说明stop调用失败.
         */
        $stopResult = $transcriber->stop();
        if (is_array($stopResult)) {
            var_dump($stopResult);
        } else {
            printf("[speechTranscriberDemo] stop failed.\n");
        }

        /**
         * 6. 释放此次实时识别的本地客户端
         */
        unset($transcriber);
        printf("[speechTranscriberDemo] finish.\n");
    }

    public static function register() {
        $file = dirname(__FILE__) . \DIRECTORY_SEPARATOR . '..' . '/nls/token/autoload.php';
        require_once $file;
        $file = dirname(__FILE__) . \DIRECTORY_SEPARATOR . '..' . '/nls/common/autoload.php';
        require_once $file;
        $file = dirname(__FILE__) . \DIRECTORY_SEPARATOR . '..' . '/nls/transport/websocket/autoload.php';
        require_once $file;
        $file = dirname(__FILE__) . \DIRECTORY_SEPARATOR . '..' . '/nls/transcriber/autoload.php';
        require_once $file;
    }
}

require_once dirname(__FILE__) . \DIRECTORY_SEPARATOR . '..' . \DIRECTORY_SEPARATOR . '/vendor/autoload.php';
SpeechTranscriberDemo::main(array_slice($argv, 1));

?>
