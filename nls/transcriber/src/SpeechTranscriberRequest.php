<?php

namespace AlibabaCloud\NLS\Transcriber;

use AlibabaCloud\NLS\Common\NlsParameters;
use WebSocket;
use AlibabaCloud\Tea\Console\Console;

class SpeechTranscriberRequest
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
    protected $_maxSentenceSilence;
    protected $_enableNlp;
    protected $_nlsModel;
    protected $_sessionId;
    protected $_payloadParam;
    protected $_contextParam;

    /**
     * 与本地服务通信的请求client
     */
    protected $_workerRequest;
    /**
     * 实时转写结果解析
     */
    protected $_transcriberParams;

    /**
     * SpeechTranscriberRequest 的构造函数
     *
     * @param int    $timeout  与本地服务链接的websocket超时时间
     * @param string $port     与本地服务链接的websocket端口
     *
     * @return SpeechTranscriberRequest
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
        $this->_transcriberParams = new SpeechTranscriberParams();
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

    public function setSemanticSentenceDetection($value)
    {
        $this->_enableSemanticsSentenceDetection = $value;
    }

    public function setMaxSentenceSilence($ms)
    {
        $this->_maxSentenceSilence = $ms;
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

    public function setEnableNlp($value)
    {
        $this->_enableNlp = $value;
    }

    public function setNlpModel($value)
    {
        $this->_nlsModel = $value;
    }

    public function setSessionId($value)
    {
        $this->_sessionId = $value;
    }

    public function AppendHttpHeaderParam($key, $value)
    {
    }

    public function setOnTaskFailed($funName, $user)
    {
        $this->_transcriberParams->setOnTaskFailed($funName, $user);
    }

    public function setOnTranscriptionStarted($funName, $user)
    {
        $this->_transcriberParams->setOnTranscriptionStarted($funName, $user);
    }

    public function setOnTranscriptionCompleted($funName, $user)
    {
        $this->_transcriberParams->setOnTranscriptionCompleted($funName, $user);
    }

    public function setOnTranscriptionResultChanged($funName, $user)
    {
        $this->_transcriberParams->setOnTranscriptionResultChanged($funName, $user);
    }

    public function setOnSentenceBegin($funName, $user)
    {
        $this->_transcriberParams->setOnSentenceBegin($funName, $user);
    }

    public function setOnSentenceEnd($funName, $user)
    {
        $this->_transcriberParams->setOnSentenceEnd($funName, $user);
    }

    public function setOnSentenceSemantics($funName, $user)
    {
        $this->_transcriberParams->setOnSentenceSemantics($funName, $user);
    }

    public function setOnChannelClosed($funName, $user)
    {
        $this->_transcriberParams->setOnChannelClosed($funName, $user);
    }

    public function setNamespace($namespace, $classname)
    {
        $this->_transcriberParams->setNamespace($namespace, $classname);
    }

    public function start()
    {
        if (isset($this->_workerRequest))
        {
            // 1. Worker切入StartTranscription模式
            $this->_workerRequest->text('StartTranscription');

            // 2. 构建Transcriber链接
            $connectCmd = json_encode([
                'uri'      => $this->_url,
                'token'    => $this->_token,
                'timeout'  => $this->_timeout / 1000
            ]);
            Console::debug("connect cmd: $connectCmd");
            $this->_workerRequest->text($connectCmd);

            // 3. 发送Transcriber Start指令
            $this->_taskId = NlsParameters::generateUuid();
            $startHeaders = NlsParameters::generateRequestHeader(
                $this->_appKey, 'StartTranscription', $this->_taskId
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
            $arrayObj = $this->_transcriberParams->parseResponse($response);
            return $arrayObj;
        }
        else
        {
            Console::error("[Transcriber] workerCmd is null");
        }
        return false;
    }

    public function stop($cancel)
    {
        if (isset($this->_workerRequest))
        {
            $this->_workerRequest->text('StopTranscription');

            $stopHeaders = NlsParameters::generateRequestHeader(
                $this->_appKey, 'StopTranscription', $this->_taskId
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
                    $curArray = $this->_transcriberParams->parseResponse($response);
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
                    $finish = $this->_transcriberParams->transcriberFinish($response);
                }
                $this->_workerRequest->text('heartbeat');
            } while ($finish === false);
            $this->_workerRequest->text('StoppedTranscription');
            Console::debug("[Transcriber] get stop response:$response");
            unset($this->_taskId);

            $this->_workerRequest->close();
            unset($this->_workerRequest);

            return $arrayObj;
        }
        else
        {
            Console::error("[Transcriber] workerCmd is null");
        }
        return false;
    }

    public function sendAudio(string $data, int $length)
    {
        $this->_workerRequest->binary($data);
        $response = $this->_workerRequest->receive(false);
        if (is_string($response))
        {
            $arrayObj = $this->_transcriberParams->parseResponse($response);
            return $arrayObj;
        }
        return true;
    }

    public function getEvent()
    {
        $this->_workerRequest->text('heartbeat');
    }
}

?>