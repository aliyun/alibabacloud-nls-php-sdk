<?php

namespace AlibabaCloud\NLS\Synthesizer;

use AlibabaCloud\NLS\Common\NlsParameters;
use WebSocket;
use AlibabaCloud\Tea\Console\Console;

class SpeechSynthesizerRequest
{
    protected $_url;
    protected $_appKey;
    protected $_token;
    protected $_timeout;
    protected $_taskId;

    protected $_format;
    protected $_sampleRate;
    protected $_pitchRate;
    protected $_speechRate;
    protected $_voice;
    protected $_volume;
    protected $_enableSubtitle;
    protected $_payloadParam;
    protected $_contextParam;

    protected $_curStatus = 'Idle';

    /**
     * 与本地服务通信的请求client
     */
    protected $_workerRequest;
    /**
     * 语音合成结果解析
     */
    protected $_synthesizerParams;

    /**
     * SpeechSynthesizerRequest 的构造函数
     *
     * @param int    $timeout  与本地服务链接的websocket超时时间
     * @param string $port     与本地服务链接的websocket端口
     *
     * @return SpeechSynthesizerRequest
     */
    public function __construct($timeout, $port)
    {
        $this->_url = "wss://nls-gateway.cn-shanghai.aliyuncs.com/ws/v1";
        $this->_sampleRate = 16000;
        $this->_format = "wav";
        $this->_pitchRate = false;
        $this->_speechRate = false;
        $this->_enableSubtitle = false;
        $this->_timeout = $timeout;

        $url = "ws://127.0.0.1:" . $port;
        $this->_workerRequest = new WebSocket\Client($url);
        $this->_synthesizerParams = new SpeechSynthesizerParams();
    }

    public function setUrl($url)
    {
        $this->_url = $url;
    }

    public function setAppKey($key)
    {
        $this->_appKey = $key;
    }

    public function setToken($token)
    {
        $this->_token = $token;
    }

    public function setFormat($format)
    {
        $this->_format = $format;
    }

    public function setSampleRate($rate)
    {
        $this->_sampleRate = $rate;
    }

    public function setText($text)
    {
        $this->_text = $text;
    }

    public function setVoice($value)
    {
        $this->_voice = $value;
    }

    public function setMethod($value)
    {
        $this->_method = $value;
    }

    public function setPitchRate($value)
    {
        $this->_pitchRate = $value;
    }

    public function setEnableSubtitle($value)
    {
        $this->_enableSubtitle = $value;
    }

    public function setSpeechRate($value)
    {
        $this->_speechRate = $value;
    }

    public function setVolume($value)
    {
        $this->_volume = $value;
    }

    public function setTimeout($ms)
    {
        $this->_timeout = $ms;
    }

    public function setPayloadParam($param)
    {
        $this->_payloadParam = $param;
    }

    public function setContextParam($param)
    {
        $this->_contextParam = $param;
    }

    public function setOnTaskFailed($funName, $user)
    {
        $this->_synthesizerParams->setOnTaskFailed($funName, $user);
    }

    public function setOnSynthesisCompleted($funName, $user)
    {
        $this->_synthesizerParams->setOnSynthesisCompleted($funName, $user);
    }

    public function setOnBinaryDataReceived($funName, $user)
    {
        $this->_synthesizerParams->setOnBinaryDataReceived($funName, $user);
    }

    public function setOnChannelClosed($funName, $user)
    {
        $this->_synthesizerParams->setOnChannelClosed($funName, $user);
    }

    public function setOnMetaInfo($funName, $user)
    {
        $this->_synthesizerParams->setOnMetaInfo($funName, $user);
    }

    public function setNamespace($namespace, $classname)
    {
        $this->_synthesizerParams->setNamespace($namespace, $classname);
    }

    public function start()
    {
        if (isset($this->_workerRequest))
        {
            if ($this->_curStatus != 'Idle')
            {
                Console::error("[Synthesizer] start status is invalid");
                return false;
            }

            // 1. Worker切入StartSynthesis模式
            $this->_workerRequest->text('StartSynthesis');

            // 2. 构建Synthesizer链接
            $connectCmd = json_encode([
                'uri'      => $this->_url,
                'token'    => $this->_token,
                'timeout'  => $this->_timeout / 1000
            ]);
            Console::debug("connect cmd: $connectCmd");
            $this->_workerRequest->text($connectCmd);

            // 3. 发送Synthesizer Start指令
            $this->_taskId = NlsParameters::generateUuid();
            $startHeaders = NlsParameters::generateRequestHeader(
                $this->_appKey, 'StartSynthesis', $this->_taskId
            );
            $startContext = NlsParameters::generateRequestContext();
            $startPayload = [
                'format'           => $this->_format,
                'sample_rate'      => $this->_sampleRate,
                'pitch_rate'       => $this->_pitchRate,
                'speech_rate'      => $this->_speechRate,
                'enable_subtitle'  => $this->_enableSubtitle,
                'text'             => $this->_text,
                'voice'            => $this->_voice,
                'volume'           => $this->_volume
            ];
            $startCmd = json_encode([
                'header'     => $startHeaders,
                'context'    => $startContext,
                'payload'    => $startPayload
            ]);
            Console::debug("start cmd: $startCmd");
            $this->_workerRequest->text($startCmd);
            $this->_curStatus = 'Started';
            return true;
        }
        else
        {
            Console::error("[Synthesizer] workerCmd is null");
            return false;
        }
    }

    public function getEvent()
    {
        if (isset($this->_workerRequest))
        {
            $this->_workerRequest->text('heartbeat');
            $response = $this->_workerRequest->receive(false);
            if (is_string($response))
            {
                $arrayObj = $this->_synthesizerParams->parseResponse($response, $this->_taskId);
                if ($this->_synthesizerParams->synthesizerFinish($response))
                {
                    $this->_workerRequest->text('StopSynthesis');
                    $this->_workerRequest->close();

                    unset($this->_taskId);
                    unset($this->_workerRequest);

                    $this->_curStatus = 'Idle';
                }
                return $arrayObj;
            }
        }
        return true;
    }
}

?>