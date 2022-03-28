<?php

namespace AlibabaCloud\NLS\Synthesizer;

use Workerman\Worker;
use WebSocket;
use AlibabaCloud\Tea\Console\Console;

/**
 * Create the local service of TTS
 * 创建语音合成请求的本地服务端, 用于与语音服务端进行通信
 */
class SpeechSynthesizerWorker
{
    protected $_curStatus;
    protected $_url;
    protected $_appKey;
    protected $_token;
    protected $_taskId;
    protected $_webClient;
    protected $_worker;
    protected $_inner_worker;

    /**
     * SpeechSynthesizerWorker 的构造函数
     *
     * @param string $port  本地服务端的websocket通信端口
     *
     * @return SpeechSynthesizerWorker
     */
    public function __construct($port)
    {
        // 初始化一个worker容器，监听port端口
        $this->_worker = new Worker('websocket://0.0.0.0:' . $port);
        $this->_worker->count = 1;
        $this->_worker->name = 'SpeechSynthesizerWorker';
        $this->_curStatus = 'Idle';

        // worker进程启动后创建一个text Worker以便打开一个内部通讯端口
        $this->_worker->onWorkerStart = function($worker)
        {
            Console::info(sprintf("[%s] worker started", $this->_worker->name));
        };

        /**
         * 当有客户端发来消息时执行的回调函数
         * status: Idle -> Connecting -> Connected -> Working
         *          ^____________________________________|
         */
        $this->_worker->onMessage = function($connection, $data)
        {
            if ($this->_curStatus === 'Idle')
            {
                if ($data == 'StartSynthesis')
                {
                    // 需要做链接, 下一个命令必须是链接参数
                    $this->_curStatus = 'Connecting';
                }
                return true;
            }
            else if ($this->_curStatus === 'Connecting')
            {
                // 做链接, 收到命令是链接参数
                $options = json_decode($data, true);
                $this->_webClient = new Websocket\Client(
                    $options['uri'], $options);
                $this->_curStatus = 'Connected';
                Console::debug(sprintf("[%s] connecting ... finish", $this->_worker->name));
                return true;
            }
            else if ($this->_curStatus === 'Connected')
            {
                if (isset($this->_webClient))
                {
                    Console::debug(sprintf("[%s] connected ... ready to start", $this->_worker->name));
                    Console::debug(sprintf("[%s] start command:%s", $this->_worker->name, $data));
                    $this->_webClient->text($data);
                    $this->_curStatus = 'Working';
                }
                else
                {
                    Console::error("webClient is null");
                    return false;
                }
                return true;
            }
            else if ($this->_curStatus === 'Working')
            {
                if (isset($this->_webClient))
                {
                    if (is_string($data) && $data === 'heartbeat')
                    {
                        $response = $this->_webClient->receive(false);
                        if (is_bool($response) && $response === false)
                        {
                            // receive zero data
                        }
                        if (is_string($response))
                        {
                            // can get data binary
                            $connection->send($response);
                        }
                    }
                    else if (is_string($data) && $data === 'StopSynthesis')
                    {
                        $response = $this->_webClient->receive(false);
                        if (is_bool($response) && $response === false)
                        {
                            // receive zero data
                        }
                        if (is_string($response))
                        {
                            $connection->send($response);
                        }
                        $this->_webClient->close();
                        unset($this->_webClient);
                        $this->_curStatus = 'Idle';
                    }
                    else
                    {
                    }
                }
                else
                {
                    Console::error("webClient is null");
                    return false;
                }
                return true;
            }
        };

        // 当有客户端连接断开时
        $this->_worker->onClose = function($connection)
        {
            Console::info(sprintf("[%s] worker closed", $this->_worker->name));
            $this->_curStatus = 'Idle';
        };
    }

    public function start()
    {
        Worker::runAll();
    }
    public function stop()
    {
    }
    public function cancel()
    {
    }
    public function close()
    {
    }
}

?>
