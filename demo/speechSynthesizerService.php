<?php

use AlibabaCloud\NLS\Synthesizer;

class SpeechSyntehsizerService {

    /**
     * @param string[] $args
     * @return void
     */
    public static function main($args){
        self::register();

        if (empty($args[1])) {
            die("Please input service port.\n");
        }

        $port = $args[1];

        /**
         * 1. 创建一个语音合成的本地服务, 通过接收客户端发来的指令, 与远端识别服务器进行通信. 
         *    注意: 不同的语音本地服务, 设入的端口必须不同
         */
        $workService = new Synthesizer\SpeechSynthesizerWorker($port);

        /**
         * 2. 启动语音合成的本地服务
         */
        $workService->start();
    }

    public static function register(){
        $file = dirname(__FILE__) . \DIRECTORY_SEPARATOR . '..' . '/nls/common/autoload.php';
        require_once $file;
        $file = dirname(__FILE__) . \DIRECTORY_SEPARATOR . '..' . '/nls/synthesizer/autoload.php';
        require_once $file;
        $file = dirname(__FILE__) . \DIRECTORY_SEPARATOR . '..' . '/nls/transport/websocket/autoload.php';
        require_once $file;
    }
}

require_once dirname(__FILE__) . \DIRECTORY_SEPARATOR . '..' . \DIRECTORY_SEPARATOR . '/vendor/autoload.php';
SpeechSyntehsizerService::main(array_slice($argv, 1));

?>
