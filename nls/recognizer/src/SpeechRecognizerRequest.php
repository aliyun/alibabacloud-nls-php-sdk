<?php

namespace AlibabaCloud\NLS\Recognizer;

use AlibabaCloud\NLS\Common\NlsParameters;
use WebSocket;
use AlibabaCloud\Tea\Console\Console;

class SpeechRecognizerRequest
{
    protected $_url;
    protected $_appKey;
    protected $_token;
    protected $_timeout;
    protected $_taskId;

    protected $_format;
    protected $_sampleRate;
    protected $_customizationId;
    protected $_vocabularyId;
    protected $_intermediateResult;
    protected $_punctuationPrediction;
    protected $_inverseTextNormalization;
    protected $_enableVoiceDetection;
    protected $_maxStartSilence;
    protected $_maxEndSilence;
    protected $_payloadParam;
    protected $_contextParam;

    /**
     * 与本地服务通信的请求client
     */
    protected $_workerRequest;
    /**
     * 一句话识别结果解析
     */
    protected $_recognizerParams;

    /**
     * SpeechRecognizerRequest 的构造函数
     *
     * @param int    $timeout  与本地服务链接的websocket超时时间
     * @param string $port     与本地服务链接的websocket端口
     *
     * @return SpeechRecognizerRequest
     */
    public function __construct($timeout, $port)
    {
        $this->_url = "wss://nls-gateway.cn-shanghai.aliyuncs.com/ws/v1";
        $this->_sampleRate = 16000;
        $this->_format = "pcm";
        $this->_intermediateResult = false;
        $this->_punctuationPrediction = false;
        $this->_inverseTextNormalization = false;
        $this->_timeout = $timeout;

        $url = "ws://127.0.0.1:" . $port;
        $this->_workerRequest = new WebSocket\Client($url);
        $this->_recognizerParams = new SpeechRecognizerParams();
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

    public function setCustomizationId($id)
    {
        $this->_customizationId = $id;
    }

    public function setVocabularyId($id)
    {
        $this->_vocabularyId = $id;
    }

    public function setIntermediateResult($result)
    {
        $this->_intermediateResult = $result;
    }

    public function setPunctuationPrediction($punctuation)
    {
        $this->_punctuationPrediction = $punctuation;
    }

    public function setInverseTextNormalization($value)
    {
        $this->_inverseTextNormalization = $value;
    }

    public function setEnableVoiceDetection($value)
    {
        $this->_enableVoiceDetection = $value;
    }

    public function setMaxStartSilence($ms)
    {
        $this->_maxStartSilence = $ms;
    }

    public function setMaxEndSilence($ms)
    {
        $this->_maxEndSilence = $ms;
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

    public function AppendHttpHeaderParam($key, $value)
    {
    }

    public function setOnTaskFailed($funName, $user)
    {
        $this->_recognizerParams->setOnTaskFailed($funName, $user);
    }

    public function setOnRecognitionStarted($funName, $user)
    {
        $this->_recognizerParams->setOnRecognitionStarted($funName, $user);
    }

    public function setOnRecognitionCompleted($funName, $user)
    {
        $this->_recognizerParams->setOnRecognitionCompleted($funName, $user);
    }

    public function setOnRecognitionResultChanged($funName, $user)
    {
        $this->_recognizerParams->setOnRecognitionResultChanged($funName, $user);
    }

    public function setOnChannelClosed($funName, $user)
    {
        $this->_recognizerParams->setOnChannelClosed($funName, $user);
    }

    public function setNamespace($namespace, $classname)
    {
        $this->_recognizerParams->setNamespace($namespace, $classname);
    }

    public function start()
    {
        if (isset($this->_workerRequest))
        {
            // 1. Worker切入StartRecognition模式
            $this->_workerRequest->text('StartRecognition');

            // 2. 构建Recognizer链接
            $connectCmd = json_encode([
                'uri'      => $this->_url,
                'token'    => $this->_token,
                'timeout'  => $this->_timeout / 1000
            ]);
            Console::debug("connect cmd: $connectCmd");
            $this->_workerRequest->text($connectCmd);

            // 3. 发送Recognizer Start指令
            $this->_taskId = NlsParameters::generateUuid();
            $startHeaders = NlsParameters::generateRequestHeader(
                $this->_appKey, 'StartRecognition', $this->_taskId
            );
            $startContext = NlsParameters::generateRequestContext();
            $startPayload = [
                'format'                               => $this->_format,
                'sample_rate'                          => $this->_sampleRate,
                'enable_intermediate_result'           => $this->_intermediateResult,
                'enable_inverse_text_normalization'    => $this->_inverseTextNormalization,
                'enable_punctuation_prediction'        => $this->_punctuationPrediction,
            ];
            $startCmd = json_encode([
                'header'     => $startHeaders,
                'context'    => $startContext,
                'payload'    => $startPayload
            ]);
            Console::debug("start cmd: $startCmd");
            $this->_workerRequest->text($startCmd);

            // 4. 发送心跳以获得Start请求后结果
            $this->_workerRequest->text('heartbeat');
            $response = $this->_workerRequest->receive(true);
            $arrayObj = $this->_recognizerParams->parseResponse($response);
            return $arrayObj;
        }
        else
        {
            Console::error("[Recognizer] workerCmd is null");
        }
        return false;
    }

    public function stop($cancel)
    {
        if (isset($this->_workerRequest))
        {
            $this->_workerRequest->text('StopRecognition');

            $stopHeaders = NlsParameters::generateRequestHeader(
                $this->_appKey, 'StopRecognition', $this->_taskId
            );
            $stopContext = NlsParameters::generateRequestContext();
            $stopCmd = json_encode([
                'header'     => $stopHeaders,
                'context'    => $stopContext,
            ]);
            Console::debug("stop cmd: $stopCmd");
            $this->_workerRequest->text($stopCmd);
            $arrayObj = null;
            $finish = false;
            $count = 0;
            do {
                $response = $this->_workerRequest->receive(false);
                if ($response === false)
                {
                    usleep(1000);
                }
                else
                {
                    $curArray = $this->_recognizerParams->parseResponse($response);
                    if (is_array($curArray))
                    {
                        $cur = array($count => $curArray);
                        $count++;
                        if ($arrayObj === null) {
                            $arrayObj = $cur;
                        } else {
                            $arrayObj = array_merge($arrayObj, $cur);
                        }
                    }
                    $finish = $this->_recognizerParams->recognizerFinish($response);
                }
                $this->_workerRequest->text('heartbeat');
            } while ($finish === false);
            $this->_workerRequest->text('StoppedRecognition');
            //Console::debug("[Recognizer] get stop response:$response");
            unset($this->_taskId);

            $this->_workerRequest->close();
            unset($this->_workerRequest);

            return $arrayObj;
        }
        else
        {
            Console::error("[Recognizer] workerCmd is null");
        }
        return false;
    }

    public function sendAudio(string $data, int $length)
    {
        $this->_workerRequest->binary($data);
        $response = $this->_workerRequest->receive(false);
        if (is_string($response))
        {
            $arrayObj = $this->_recognizerParams->parseResponse($response);
            return $arrayObj;
        }
        return true;
    }
}

?>