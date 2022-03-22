<?php

namespace AlibabaCloud\NLS\Transcriber;

/**
 * Create the client of RT-ASR
 * 创建实时语音识别请求的客户端
 */
class SpeechTranscriberClient
{
    protected $_request;

    /**
     * SpeechTranscriberClient constructor.
     * SpeechTranscriberClient 的构造函数
     *
     * @param string $port  the websocket port of this request client.
     * @param string $port  本请求客户端的websocket通信端口
     *
     * @return SpeechTranscriberRequest
     */
    public function __construct($port)
    {
        $this->_request = new SpeechTranscriberRequest(5000, $port);
    }

    /**
     * setUrl  set the URL of RT-ASR service.
     * setUrl  设置实时音频流识别服务URL地址, 不调用的情况下默认为公网服务URL地址
     *
     * @param string $url  the URL of RT-ASR service
     * @param string $url  实时语音识别的服务URL地址
     */
    public function setUrl($url)
    {
        $this->_request->setUrl($url);
    }

    /**
     * setAppKey  set the appkey of your project.
     * setAppKey  设置管控台创建的项目appkey
     *
     * @param string $key  the appkey of your project
     * @param string $key  管控台创建的项目appkey
     */
    public function setAppKey($key)
    {
        $this->_request->setAppKey($key);
    }

    /**
     * setToken  set the access token of your project. detail in NlsToken.php
     * setToken  设置访问令牌. 访问令牌创建方法见NlsToken.php 
     *
     * @param string $token  the appkey of your project
     * @param string $token  管控台创建的项目appkey
     */
    public function setToken($token)
    {
        $this->_request->setToken($token);
    }

    /**
     * setFormat  set the format of audio data, PCM is the only support now
     * setFormat  设置输入音频数据的编码格式, 当前只支持PCM. 可不调用.
     *
     * @param string $format  the format of audio data
     * @param string $format  音频数据格式
     */
    public function setFormat($format)
    {
        $this->_request->setFormat($format);
    }

    /**
     * setSampleRate  set the sample rate of audio, 16000 is default
     * setSampleRate  设置音频数据采样率, 默认16000
     *
     * @param int $rate  the sample rate of audio
     * @param int $rate  音频采样率
     */
    public function setSampleRate($rate)
    {
        $this->_request->setSampleRate($rate);
    }

    /**
     * setCustomizationId  设置定制模型
     *
     * @param string $id  定制模型id
     */
    public function setCustomizationId($id)
    {
        $this->_request->setCustomizationId($id);
    }

    /**
     * setVocabularyId  设置泛热词
     *
     * @param string $id  定制泛热词id
     */
    public function setVocabularyId($id)
    {
        $this->_request->setVocabularyId($id);
    }

    /**
     * setIntermediateResult  设置是否返回中间识别结果, 可选参数, 默认false
     *
     * @param bool $enable  是否返回中间识别结果
     */
    public function setIntermediateResult($enable)
    {
        $this->_request->setIntermediateResult($enable);
    }

    /**
     * setPunctuationPrediction  设置是否在后处理中添加标点, 可选参数, 默认false
     *
     * @param bool $enable  是否在后处理中添加标点
     */
    public function setPunctuationPrediction($enable)
    {
        $this->_request->setPunctuationPrediction($enable);
    }

    /**
     * setInverseTextNormalization  设置是否在后处理中执行数字转换, 可选参数, 默认false
     *
     * @param bool $enable  是否在后处理中执行数字转换
     */
    public function setInverseTextNormalization($enable)
    {
        $this->_request->setInverseTextNormalization($enable);
    }

    /**
     * setMaxSentenceSilence  设置vad阀值
     *
     * @param int $ms  可选参数.
     *                 静音时长超过该阈值会被认为断句, 合法参数范围200～6000(ms), 默认值800ms.
     *                 vad断句与语义断句为互斥关系, 不能同时使用.
     *                 调用此设置前, 请将语义断句setSemanticSentenceDetection设置为false.
     */
    public function setMaxSentenceSilence($ms)
    {
        $this->_request->setMaxSentenceSilence($ms);
    }

    /**
     * setTimeout  设置Socket接收超时时间
     *
     * @param int $ms  超时时间, 单位毫秒
     */
    public function setTimeout($ms)
    {
        $this->_request->setTimeout($ms);
    }

    /**
     * setPayloadParam  参数设置
     *
     * @param string $param  参数json字符串
     */
    public function setPayloadParam($param)
    {
        $this->_request->setPayloadParam($param);
    }

    /**
     * setContextParam  设置用户自定义参数
     *
     * @param string $param  参数json字符串
     */
    public function setContextParam($param)
    {
        $this->_request->setContextParam($param);
    }

    /**
     * setEnableNlp  设置是否开启nlp服务
     *
     * @param bool $enable  是否开启nlp服务
     */
    public function setEnableNlp($enable)
    {
        $this->_request->setEnableNlp($enable);
    }

    /**
     * setNlpModel  设置nlp模型名称，开启NLP服务后必填
     *
     * @param string $value  nlp模型名称
     */
    public function setNlpModel($value)
    {
        $this->_request->setNlpModel($value);
    }

    /**
     * setSessionId  设置session id, 用于请求异常断开重连时, 服务端识别是同一个会话。
     *
     * @param string $value  session id 字符串
     */
    public function setSessionId($value)
    {
        $this->_request->setSessionId($value);
    }

    /**
     * AppendHttpHeaderParam  设置用户自定义ws阶段http header参数. 暂不支持
     *
     * @param string $key    参数名称
     * @param string $value  参数内容
     */
    public function AppendHttpHeaderParam($key, $value)
    {
    }

    /**
     * setNamespace  设置回调函数的命名空间和所述类名, 
     *               设置回调函数后必须正确调用此函数, 否则回调函数无法正常回调
     *
     * @param string $namespace  回调函数的命名空间
     * @param string $classname  回调函数的所述类名
     */
    public function setNamespace($namespace, $classname)
    {
        $this->_request->setNamespace($namespace, $classname);
    }

    /**
     * setOnTaskFailed  设置错误回调函数.
     *                  在请求过程中出现异常错误时，sdk内部线程上报该回调。
     *                  用户可以在事件的消息头中检查状态码和状态消息，以确认失败的具体原因.
     *
     * @param string $funName  回调方法
     * @param mixed  $user     用户传入参数
     */
    public function setOnTaskFailed($funName, $user)
    {
        $this->_request->setOnTaskFailed($funName, $user);
    }

    /**
     * setOnTranscriptionStarted  设置实时音频流识别开始回调函数.
     *                            服务端就绪、可以开始识别时，sdk内部线程上报该回调.
     *
     * @param string $funName  回调方法
     * @param mixed  $user     用户传入参数
     */
    public function setOnTranscriptionStarted($funName, $user)
    {
        $this->_request->setOnTranscriptionStarted($funName, $user);
    }

    /**
     * setOnTranscriptionCompleted  设置服务端结束服务回调函数.
     *                              云端结束实时音频流识别服务时, sdk内部线程上报该回调.
     *
     * @param string $funName  回调方法
     * @param mixed  $user     用户传入参数
     */
    public function setOnTranscriptionCompleted($funName, $user)
    {
        $this->_request->setOnTranscriptionCompleted($funName, $user);
    }

    /**
     * setOnTranscriptionResultChanged  设置实时音频流识别中间结果回调函数.
     *                                  setIntermediateResult设置为true，才会有中间结果.
     *
     * @param string $funName  回调方法
     * @param mixed  $user     用户传入参数
     */
    public function setOnTranscriptionResultChanged($funName, $user)
    {
        $this->_request->setOnTranscriptionResultChanged($funName, $user);
    }

    /**
     * setOnSentenceBegin  设置一句话开始回调.
     *                     检测到一句话的开始时, sdk内部线程上报该回调.
     *
     * @param string $funName  回调方法
     * @param mixed  $user     用户传入参数
     */
    public function setOnSentenceBegin($funName, $user)
    {
        $this->_request->setOnSentenceBegin($funName, $user);
    }

    /**
     * setOnSentenceEnd  设置一句话结束回调函数.
     *                   检测到了一句话的结束时, sdk内部线程上报该回调.
     *
     * @param string $funName  回调方法
     * @param mixed  $user     用户传入参数
     */
    public function setOnSentenceEnd($funName, $user)
    {
        $this->_request->setOnSentenceEnd($funName, $user);
    }

    /**
     * setOnChannelClosed  设置通道关闭回调函数.
     *                     在请求过程中通道关闭时，sdk内部线程上报该回调.
     *
     * @param string $funName  回调方法
     * @param mixed  $user     用户传入参数
     */
    public function setOnChannelClosed($funName, $user)
    {
        $this->_request->setOnChannelClosed($funName, $user);
    }

    /**
     * setOnSentenceSemantics  设置二次处理结果回调函数
     *                         表示对实时转写的原始结果进行处理后的结果, setEnableNlp设置true后返回
     *
     * @param string $funName  回调方法
     * @param mixed  $user     用户传入参数
     */
    public function setOnSentenceSemantics($funName, $user)
    {
        $this->_request->setOnSentenceSemantics($funName, $user);
    }

    /**
     * start  启动实时音频流识别. 若设置了回调, 在返回所有事件的解析array同时返回相关回调
     *
     * @return array|bool 若成功, 返回所有事件的解析array, 同时若设置了回调会返回相关结果.
     *                    若失败, 返回false
     */
    public function start()
    {
        return $this->_request->start();
    }

    /**
     * stop  会与服务端确认关闭，正常停止实时音频流识别操作. 
     *       若设置了回调, 在返回所有事件的解析array同时返回相关回调
     *
     * @return array|bool 若成功, 返回所有事件的解析array, 同时若设置了回调会返回相关结果.
     *                    若失败, 返回false
     */
    public function stop()
    {
        return $this->_request->stop(false);
    }

    /**
     * cancel  直接关闭实时音频流识别过程, 调用cancel之后不会在上报任何回调事件
     *
     * @return bool
     */
    public function cancel()
    {
        return $this->_request->stop(true);
    }

    /**
     * sendAudio  发送语音数据, 当前只支持PCM格式.
     *
     * @param string $data    音频数据
     * @param int    $length  语音数据长度(建议每次100ms数据, 单通道16K16bit100ms音频 = 3200bytes)
     *
     * @return array|bool 若成功, 返回所有事件的解析array, 同时若设置了回调会返回相关结果.
     *                    若失败, 返回false. 若成功但无事件, 返回true.
     */
    public function sendAudio(string $data, int $length)
    {
        return $this->_request->sendAudio($data, $length);
    }

    /**
     * getEvent  获取当前服务中的事件信息.
     *
     * @return array 若成功, 返回所有事件的解析array, 同时若设置了回调会返回相关结果.
     */
    public function getEvent()
    {
        $this->_request->getEvent();
    }
}

?>