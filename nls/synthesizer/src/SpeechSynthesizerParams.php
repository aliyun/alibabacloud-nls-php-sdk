<?php

namespace AlibabaCloud\NLS\Synthesizer;

use AlibabaCloud\Tea\Console\Console;
use AlibabaCloud\NLS\Common\NlsEvent;

class SpeechSynthesizerParams
{
    protected $_user;
    protected $_namespace;
    protected $_classname;

    protected $_onTaskFailed;
    protected $_onSynthesisCompleted;
    protected $_onBinaryDataReceived;
    protected $_onChannelClosed;
    protected $_onMetaInfo;

    public function __construct()
    {
    }

    /**
     * synthesizerFinish  判断语音合成是否结束.
     *
     * @param string $response  从服务端获取到的json字符串格式的结果
     * 
     * @return bool  该轮语音合成结束则返回true, 未结束则返回false.
     */
    public function synthesizerFinish($response)
    {
        $result = $this->getHeaderName($response);
        if ($result === false)
        {
            return false;
        }
        else
        {
            if ($result === 'SynthesisCompleted' ||
                $result === 'TaskFailed')
            {
                return true;
            }
        }
        return false;
    }

    /**
     * setNamespace  设置回调函数的命名空间和所述类名
     *
     * @param string $namespace  回调函数的命名空间
     * @param string $classname  回调函数的所述类名
     */
    public function setNamespace($namespace, $classname)
    {
        $this->_namespace = $namespace;
        $this->_classname = $classname;
    }

    /**
     * setOnTaskFailed  设置错误回调函数.
     *
     * @param string $funName  回调方法
     * @param mixed  $user     用户传入参数
     */
    public function setOnTaskFailed($funName, $user)
    {
        $this->_user = $user;
        $this->_onTaskFailed = $funName;
    }

    /**
     * setOnSynthesisCompleted  设置服务端结束服务回调函数.
     *
     * @param string $funName  回调方法
     * @param mixed  $user     用户传入参数
     */
    public function setOnSynthesisCompleted($funName, $user)
    {
        $this->_user = $user;
        $this->_onSynthesisCompleted = $funName;
    }

    /**
     * setOnBinaryDataReceived  设置语音合成二进制音频数据接收回调函数.
     *
     * @param string $funName  回调方法
     * @param mixed  $user     用户传入参数
     */
    public function setOnBinaryDataReceived($funName, $user)
    {
        $this->_user = $user;
        $this->_onBinaryDataReceived = $funName;
    }

    /**
     * setOnChannelClosed  设置通道关闭回调函数.
     *
     * @param string $funName  回调方法
     * @param mixed  $user     用户传入参数
     */
    public function setOnChannelClosed($funName, $user)
    {
        $this->_user = $user;
        $this->_onChannelClosed = $funName;
    }

    /**
     * setOnMetaInfo  设置文本对应的日志信息接收回调函数.
     *
     * @param string $funName  回调方法
     * @param mixed  $user     用户传入参数
     */
    public function setOnMetaInfo($funName, $user)
    {
        $this->_user = $user;
        $this->_onMetaInfo = $funName;
    }

    /**
     * parseResponse  解析服务端返回的json字符串内容
     *
     * @param string $response  服务端返回的json字符串
     *
     * @return array|bool 若成功, 返回所有事件的解析array, 同时若设置了回调会返回相关结果.
     *                    若失败, 返回false. 若成功但无事件, 返回true.
     */
    public function parseResponse($response, $taskId): array
    {
        $event = new NlsEvent();
        $responseName = $this->getHeaderName($response);
        if ($responseName === false)
        {
            return $this->parseBinary($response, $event, $taskId);
        }
        if ($responseName === 'MetaInfo')
        {
            return $this->parseMetaInfo($response, $event);
        }
        else if ($responseName === 'TaskFailed')
        {
            return $this->parseTaskFailed($response, $event);
        }
        else if ($responseName === 'SynthesisCompleted')
        {
            return $this->parseSynthesisCompleted($response, $event);
        }
        else
        {
            Console::error("[parseResponse] unknow response name:$responseName");
        }
        return false;
    }

    /**
     * getHeaderName  解析服务端返回的json字符串内容获得其中header信息
     *
     * @param string $response  服务端返回的json字符串
     *
     * @return string|bool 若成功, 返回事件的名称字符串.
     *                     若失败, 返回false.
     */
    public function getHeaderName($response)
    {
        if (is_string($response))
        {
            if ($this->is_json($response)) {
                $jsonArray = json_decode($response, true);
                $header = $jsonArray['header'];
                $name = $header['name'];
                return $name;
            } else {
                // 非json, 表示为binary
                return false;
            }
        }
        else
        {
            return false;
        }
    }

    /**
     * parseMetaInfo  解析服务端返回的MetaInfo事件的json字符串内容
     *
     * @param string   $response  服务端返回的json字符串
     * @param NlsEvent $event     NLS事件参数对象
     *
     * @return array|bool 若成功, 返回所有事件的解析array, 同时若设置了回调会返回相关结果.
     *                    若失败, 返回false. 若成功但无事件, 返回true.
     */
    public function parseMetaInfo($response, $event): array
    {
        if ($this->is_json($response))
        {
            $jsonArray = json_decode($response, true);
            $header = $jsonArray['header'];
            $payload = $jsonArray['payload'];

            $event->_statusCode = $header['status'];
            $event->_statusText = $header['status_text'];
            $event->_taskId = $header['task_id'];
            $event->_message_id = $header['message_id'];
            $event->_msgType = $header['name'];
            $event->_subtitles = $payload['subtitles'];
            $event->_msg = $response;

            if (isset($this->_onMetaInfo))
            {
                call_user_func(
                    array($this->_namespace. '\\' . $this->_classname, $this->_onMetaInfo),
                    $event);
            }

            $arrayObj = array(
                'status_code' => $event->_statusCode,
                'status_text' => $event->_statusText,
                'task_id'     => $event->_taskId,
                'message_id'  => $event->_message_id,
                'event_name'  => $event->_msgType,
                'subtitles'   => $event->_subtitles,
                'response'    => $event->_msg
            );
            return $arrayObj;
        }
        else
        {
            return false;
        }
    }
    /**
     * parseTaskFailed  解析服务端返回的语音合成失败事件的json字符串内容
     *
     * @param string   $response  服务端返回的json字符串
     * @param NlsEvent $event     NLS事件参数对象
     *
     * @return array|bool 若成功, 返回所有事件的解析array, 同时若设置了回调会返回相关结果.
     *                    若失败, 返回false. 若成功但无事件, 返回true.
     */
    public function parseTaskFailed($response, $event): array
    {
        if ($this->is_json($response))
        {
            $jsonArray = json_decode($response, true);
            $header = $jsonArray['header'];

            $event->_statusCode = $header['status'];
            $event->_taskId = $header['task_id'];
            $event->_msgType = $header['name'];
            $event->_statusText = $header['status_text'];
            $event->_message_id = $header['message_id'];
            $event->_msg = $response;

            if (isset($this->_onTaskFailed))
            {
                call_user_func(
                    array($this->_namespace. '\\' . $this->_classname, $this->_onTaskFailed),
                    $event);
            }

            $arrayObj = array(
                'status_code' => $event->_statusCode,
                'status_text' => $event->_statusText,
                'task_id'     => $event->_taskId,
                'message_id'  => $event->_message_id,
                'event_name'  => $event->_msgType,
                'response'    => $event->_msg
            );
            return $arrayObj;
        }
        else 
        {
            return false;
        }
    }
    /**
     * parseSynthesisCompleted  解析服务端返回的语音合成完成事件的json字符串内容
     *
     * @param string   $response  服务端返回的json字符串
     * @param NlsEvent $event     NLS事件参数对象
     *
     * @return array|bool 若成功, 返回所有事件的解析array, 同时若设置了回调会返回相关结果.
     *                    若失败, 返回false. 若成功但无事件, 返回true.
     */
    public function parseSynthesisCompleted($response, $event): array
    {
        if ($this->is_json($response))
        {
            $jsonArray = json_decode($response, true);
            $header = $jsonArray['header'];

            $event->_statusCode = $header['status'];
            $event->_statusText = $header['status_text'];
            $event->_taskId = $header['task_id'];
            $event->_message_id = $header['message_id'];
            $event->_msgType = $header['name'];
            $event->_msg = $response;

            if (isset($this->_onSynthesisCompleted))
            {
                call_user_func(
                    array($this->_namespace. '\\' . $this->_classname, $this->_onSynthesisCompleted),
                    $event);
            }

            $arrayObj = array(
                'status_code' => $event->_statusCode,
                'status_text' => $event->_statusText,
                'task_id'     => $event->_taskId,
                'message_id'  => $event->_message_id,
                'event_name'  => $event->_msgType,
                'response'    => $event->_msg
            );
            return $arrayObj;
        }
        else
        {
            return false;
        }
    }
    /**
     * parseOnChannelClosed  解析服务端返回的语音合成结束事件的json字符串内容
     *
     * @param string   $response  服务端返回的json字符串
     * @param NlsEvent $event     NLS事件参数对象
     *
     * @return array|bool 若成功, 返回所有事件的解析array, 同时若设置了回调会返回相关结果.
     *                    若失败, 返回false. 若成功但无事件, 返回true.
     */
    public function parseOnChannelClosed($response, $event)
    {
        if ($this->is_json($response))
        {
            $jsonArray = json_decode($response, true);
            $header = $jsonArray['header'];

            $event->_statusCode = $header['status'];
            $event->_taskId = $header['task_id'];
            $event->_msgType = $header['name'];
            $event->_msg = $response;

            if (isset($this->_onChannelClosed))
            {
                call_user_func(
                    array($this->_namespace. '\\' . $this->_classname, $this->_onChannelClosed),
                    $event);
            }
        }
        else
        {
            return false;
        }
    }
    /**
     * parseBinary  解析服务端返回的语音合成的音频数据的json字符串内容
     *
     * @param string   $response  服务端返回的json字符串
     * @param NlsEvent $event     NLS事件参数对象
     *
     * @return array|bool 若成功, 返回所有事件的解析array, 同时若设置了回调会返回相关结果.
     *                    若失败, 返回false. 若成功但无事件, 返回true.
     */
    public function parseBinary($response, $event, $taskId)
    {
        if (strlen($response) > 0)
        {
            $event->_taskId = $taskId;
            $event->_data = $response;
            $event->_dataSize = strlen($response);

            if (isset($this->_onBinaryDataReceived))
            {
                call_user_func(
                    array($this->_namespace. '\\' . $this->_classname, $this->_onBinaryDataReceived),
                    $event);
            }

            $arrayObj = array(
                'status_code' => 20000000,
                'status_text' => "Gateway:SUCCESS:Success.",
                'task_id'     => $event->_taskId,
                'event_name'  => "SynthesisBinaryReceived",
                'data'        => $event->_data,
                'data_size'   => $event->_dataSize
            );
            return $arrayObj;
        }
        else
        {
            return false;
        }
    }

    /**
     * is_json  判断字符串是否为json格式
     *
     * @param string  $string  服务端返回的json字符串
     *
     * @return bool  若不是json格式字符串, 返回false. 若是json格式字符串, 返回true.
     */
    private function is_json($string)
    {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }
}

?>
