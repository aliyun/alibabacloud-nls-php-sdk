<?php

namespace AlibabaCloud\NLS\Synthesizer;

/**
 * Create the client of TTS
 * 创建语音合成请求的客户端
 */
class SpeechSynthesizerClient
{
    protected $_request;

    /**
     * SpeechSynthesizerClient constructor.
     * SpeechSynthesizerClient 的构造函数
     *
     * @param string $port  the websocket port of this request client.
     * @param string $port  本请求客户端的websocket通信端口
     *
     * @return SpeechSynthesizerRequest
     */
    public function __construct($port)
    {
        $this->_request = new SpeechSynthesizerRequest(5000, $port);
    }

    /**
     * setUrl  set the URL of TTS service.
     * setUrl  设置语音合成服务URL地址, 不调用的情况下默认为公网服务URL地址
     *
     * @param string $url  the URL of TTS service
     * @param string $url  语音合成的服务URL地址
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
     * setFormat  set the format of audio data
     * setFormat  设置输出音频数据的编码格式. 
     *            可选参数, 默认是pcm. 支持的格式pcm, wav, mp3
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
     * setText  待合成音频文本内容text设置,
     *          必选参数，需要传入UTF-8编码的文本内容
     *
     * @param string $text  音频文本内容
     */
    public function setText($text)
    {
        $this->_request->setText($text);
    }

    /**
     * setVoice  发音人voice设置,
     *           包含"xiaoyun", "xiaogang". 可选参数, 默认是xiaoyun.
     *
     * @param string $value  发音人字符串
     */
    public function setVoice($value)
    {
        $this->_request->setVoice($value);
    }

    /**
     * setMethod  合成方法method设置, 可选参数, 默认是0.
     *            0 统计参数合成: 基于统计参数的语音合成,
     *              优点是能适应的韵律特征的范围较宽,
     *              合成器比特率低, 资源占用小, 性能高, 音质适中.
     *            1 波形拼接合成: 基于高质量音库提取学习合成,
     *              资源占用相对较高, 音质较好, 更加贴近真实发音,
     *              但没有参数合成稳定
     *
     * @param int $method
     */
    public function setMethod($method)
    {
        $this->_request->setMethod($method);
    }

    /**
     * setPitchRate  语调pitch_rate设置, 范围是-500~500,
     *               可选参数, 默认是0
     *
     * @param int $rate
     */
    public function setPitchRate($rate)
    {
        $this->_request->setPitchRate($rate);
    }

    /**
     * setEnableSubtitle  是否开启字幕功能
     *
     * @param bool $enable
     */
    public function setEnableSubtitle($enable)
    {
        $this->_request->setEnableSubtitle($enable);
    }

    /**
     * setSpeechRate  语速speech_rate设置,
     *                范围是-500~500, 可选参数, 默认是0
     *
     * @param int $rate
     */
    public function setSpeechRate($rate)
    {
        $this->_request->setSpeechRate($rate);
    }

    /**
     * setVolume  音量volume设置,
     *            范围是0~100, 可选参数, 默认50
     *
     * @param int $volume
     */
    public function setVolume($volume)
    {
        $this->_request->setVolume($volume);
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
     * setOnSynthesisCompleted  设置服务端结束服务回调函数.
     *                          云端结束语音合成服务时, sdk内部线程上报该回调.
     *
     * @param string $funName  回调方法
     * @param mixed  $user     用户传入参数
     */
    public function setOnSynthesisCompleted($funName, $user)
    {
        $this->_request->setOnSynthesisCompleted($funName, $user);
    }

    /**
     * setOnBinaryDataReceived  设置语音合成二进制音频数据接收回调函数.
     *                          接收到服务端发送的二进制音频数据时，sdk内部线程上报该回调函数.
     *
     * @param string $funName  回调方法
     * @param mixed  $user     用户传入参数
     */
    public function setOnBinaryDataReceived($funName, $user)
    {
        $this->_request->setOnBinaryDataReceived($funName, $user);
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
     * setOnMetaInfo  设置文本对应的日志信息接收回调函数.
     *                接收到服务端送回文本对应的日志信息,
     *                增量返回对应的字幕信息时, sdk内部线程上报该回调函数.
     *
     * @param string $funName  回调方法
     * @param mixed  $user     用户传入参数
     */
    public function setOnMetaInfo($funName, $user)
    {
        $this->_request->setOnMetaInfo($funName, $user);
    }

    /**
     * start  启动语音合成. 若设置了回调, 在返回所有事件的解析array同时返回相关回调
     *
     * @return array|bool 若成功, 返回所有事件的解析array, 同时若设置了回调会返回相关结果.
     *                    若失败, 返回false
     */
    public function start()
    {
        return $this->_request->start();
    }

    /**
     * getEvent  获取当前服务中的事件信息.
     *           整个语音合成过程需要不停轮询该接口, 否则无法获得语音合成结果.
     *
     * @return array 若成功, 返回所有事件的解析array, 同时若设置了回调会返回相关结果.
     */
    public function getEvent()
    {
        return $this->_request->getEvent();
    }
}

?>