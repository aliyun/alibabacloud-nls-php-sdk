<?php

namespace AlibabaCloud\NLS\Recognizer;

use AlibabaCloud\Tea\Console\Console;
use AlibabaCloud\NLS\Common\NlsEvent;

class SpeechRecognizerParams
{
    protected $_user;
    protected $_namespace;
    protected $_classname;

    protected $_onTaskFailed;
    protected $_onRecognitionStarted;
    protected $_onRecognitionCompleted;
    protected $_onRecognitionResultChanged;
    protected $_onChannelClosed;

    public function __construct()
    {
    }

    /**
     * recognizerFinish  判断一句话识别是否结束.
     *
     * @param string $response  从服务端获取到的json字符串格式的结果
     * 
     * @return bool  该轮一句话识别结束则返回true, 未结束则返回false.
     */
    public function recognizerFinish($response)
    {
        $result = $this->getHeaderName($response);
        if ($result === false)
        {
            return false;
        }
        else
        {
            if ($result === 'RecognitionCompleted' ||
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
     * setOnRecognitionStarted  设置一句话识别开始回调函数.
     *
     * @param string $funName  回调方法
     * @param mixed  $user     用户传入参数
     */
    public function setOnRecognitionStarted($funName, $user)
    {
        $this->_user = $user;
        $this->_onRecognitionStarted = $funName;
    }

    /**
     * setOnRecognitionCompleted  设置服务端结束服务回调函数.
     *
     * @param string $funName  回调方法
     * @param mixed  $user     用户传入参数
     */
    public function setOnRecognitionCompleted($funName, $user)
    {
        $this->_user = $user;
        $this->_onRecognitionCompleted = $funName;
    }

    /**
     * setOnRecognitionResultChanged  设置一句话识别中间结果回调函数.
     *
     * @param string $funName  回调方法
     * @param mixed  $user     用户传入参数
     */
    public function setOnRecognitionResultChanged($funName, $user)
    {
        $this->_user = $user;
        $this->_onRecognitionResultChanged = $funName;
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
     * parseResponse  解析服务端返回的json字符串内容
     *
     * @param string $response  服务端返回的json字符串
     *
     * @return array|bool 若成功, 返回所有事件的解析array, 同时若设置了回调会返回相关结果.
     *                    若失败, 返回false. 若成功但无事件, 返回true.
     */
    public function parseResponse($response): array
    {
        $event = new NlsEvent();
        $responseName = $this->getHeaderName($response);
        if ($responseName === false)
        {
            return false;
        }
        if ($responseName === 'RecognitionStarted')
        {
            return $this->parseRecognitionStarted($response, $event);
        }
        else if ($responseName === 'RecognitionResultChanged')
        {
            return $this->parseResultChanged($response, $event);
        }
        else if ($responseName === 'RecognitionCompleted')
        {
            return $this->parseRecognitionCompleted($response, $event);
        }
        else if ($responseName === 'TaskFailed')
        {
            return $this->parseTaskFailed($response, $event);
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
     * @return string|bool 若成功, 返回事件的名称字符串
     *                     若失败, 返回false.
     */
    public function getHeaderName($response)
    {
        if (is_string($response) && $this->is_json($response))
        {
            $jsonArray = json_decode($response, true);
            $header = $jsonArray['header'];
            $name = $header['name'];
            return $name;
        }
        else
        {
            return false;
        }
    }

    /**
     * parseRecognitionStarted  解析服务端返回的start事件的json字符串内容
     *
     * @param string   $response  服务端返回的json字符串
     * @param NlsEvent $event     NLS事件参数对象
     *
     * @return array|bool 若成功, 返回所有事件的解析array, 同时若设置了回调会返回相关结果.
     *                    若失败, 返回false. 若成功但无事件, 返回true.
     */
    public function parseRecognitionStarted($response, $event): array
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

            if (isset($this->_onRecognitionStarted))
            {
                call_user_func(
                    array($this->_namespace. '\\' . $this->_classname, $this->_onRecognitionStarted),
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
     * parseResultChanged  解析服务端返回的中间结果事件的json字符串内容
     *
     * @param string   $response  服务端返回的json字符串
     * @param NlsEvent $event     NLS事件参数对象
     *
     * @return array|bool 若成功, 返回所有事件的解析array, 同时若设置了回调会返回相关结果.
     *                    若失败, 返回false. 若成功但无事件, 返回true.
     */
    public function parseResultChanged($response, $event): array
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
            $event->_duration = $payload['duration'];
            $event->_result = $payload['result'];
            $event->_msg = $response;

            if (isset($this->_onRecognitionResultChanged))
            {
                call_user_func(
                    array($this->_namespace. '\\' . $this->_classname, $this->_onRecognitionResultChanged),
                    $event);
            }

            $arrayObj = array(
                'status_code' => $event->_statusCode,
                'status_text' => $event->_statusText,
                'task_id'     => $event->_taskId,
                'message_id'  => $event->_message_id,
                'event_name'  => $event->_msgType,
                'duration'    => $event->_duration,
                'result'      => $event->_result,
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
     * parseResultChanged  解析服务端返回的识别失败事件的json字符串内容
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
            $event->_statusText = $header['status_text'];
            $event->_taskId = $header['task_id'];
            $event->_message_id = $header['message_id'];
            $event->_msgType = $header['name'];
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
     * parseRecognitionCompleted  解析服务端返回的一句话识别完成事件的json字符串内容
     *
     * @param string   $response  服务端返回的json字符串
     * @param NlsEvent $event     NLS事件参数对象
     *
     * @return array|bool 若成功, 返回所有事件的解析array, 同时若设置了回调会返回相关结果.
     *                    若失败, 返回false. 若成功但无事件, 返回true.
     */
    public function parseRecognitionCompleted($response, $event): array
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
            $event->_duration = $payload['duration'];
            $event->_result = $payload['result'];
            $event->_msg = $response;

            if (isset($this->_onRecognitionCompleted))
            {
                call_user_func(
                    array($this->_namespace. '\\' . $this->_classname, $this->_onRecognitionCompleted),
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
     * parseOnChannelClosed  解析服务端返回的一句话识别结束事件的json字符串内容
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
