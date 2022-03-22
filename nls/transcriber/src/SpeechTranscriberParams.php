<?php

namespace AlibabaCloud\NLS\Transcriber;

use AlibabaCloud\Tea\Console\Console;
use AlibabaCloud\NLS\Common\NlsEvent;

class SpeechTranscriberParams
{
    protected $_user;
    protected $_namespace;
    protected $_classname;

    protected $_onTaskFailed;
    protected $_onTranscriptionStarted;
    protected $_onTranscriptionCompleted;
    protected $_onTranscriptionResultChanged;
    protected $_onSentenceBegin;
    protected $_onSentenceEnd;
    protected $_onSentenceSemantics;
    protected $_onChannelClosed;

    public function __construct()
    {
    }

    /**
     * transcriberFinish  判断实时识别是否结束.
     *
     * @param string $response  从服务端获取到的json字符串格式的结果
     * 
     * @return bool  该轮实时识别结束则返回true, 未结束则返回false.
     */
    public function transcriberFinish($response)
    {
        $result = $this->getHeaderName($response);
        if ($result === false)
        {
            return false;
        }
        else
        {
            if ($result === 'TranscriptionCompleted' ||
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
     * setOnTranscriptionStarted  设置实时音频流识别开始回调函数.
     *
     * @param string $funName  回调方法
     * @param mixed  $user     用户传入参数
     */
    public function setOnTranscriptionStarted($funName, $user)
    {
        $this->_user = $user;
        $this->_onTranscriptionStarted = $funName;
    }

    /**
     * setOnTranscriptionCompleted  设置服务端结束服务回调函数.
     *
     * @param string $funName  回调方法
     * @param mixed  $user     用户传入参数
     */
    public function setOnTranscriptionCompleted($funName, $user)
    {
        $this->_user = $user;
        $this->_onTranscriptionCompleted = $funName;
    }

    /**
     * setOnTranscriptionResultChanged  设置实时音频流识别中间结果回调函数.
     *
     * @param string $funName  回调方法
     * @param mixed  $user     用户传入参数
     */
    public function setOnTranscriptionResultChanged($funName, $user)
    {
        $this->_user = $user;
        $this->_onTranscriptionResultChanged = $funName;
    }

    /**
     * setOnSentenceBegin  设置一句话开始回调.
     *
     * @param string $funName  回调方法
     * @param mixed  $user     用户传入参数
     */
    public function setOnSentenceBegin($funName, $user)
    {
        $this->_user = $user;
        $this->_onSentenceBegin = $funName;
    }

    /**
     * setOnSentenceEnd  设置一句话结束回调函数.
     *
     * @param string $funName  回调方法
     * @param mixed  $user     用户传入参数
     */
    public function setOnSentenceEnd($funName, $user)
    {
        $this->_user = $user;
        $this->_onSentenceEnd = $funName;
    }

    /**
     * setOnSentenceSemantics  设置二次处理结果回调函数
     *
     * @param string $funName  回调方法
     * @param mixed  $user     用户传入参数
     */
    public function setOnSentenceSemantics($funName, $user)
    {
        $this->_user = $user;
        $this->_onSentenceSemantics = $funName;
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
    public function parseResponse($response)
    {
        $event = new NlsEvent();
        $responseName = $this->getHeaderName($response);
        if ($responseName === false)
        {
            return false;
        }
        if ($responseName === 'TranscriptionStarted')
        {
            return $this->parseTranscriptionStarted($response, $event);
        }
        else if ($responseName === 'TranscriptionResultChanged')
        {
            return $this->parseResultChanged($response, $event);
        }
        else if ($responseName === 'TranscriptionCompleted')
        {
            return $this->parseTranscriptionCompleted($response, $event);
        }
        else if ($responseName === 'SentenceBegin')
        {
            return $this->parseSentenceBegin($response, $event);
        }
        else if ($responseName === 'SentenceEnd')
        {
            return $this->parseSentenceEnd($response, $event);
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
     * @return string|bool 若成功, 返回事件的名称字符串. 若失败, 返回false.
     */
    public function getHeaderName($response)
    {
        if (is_string($response))
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
     * parseTranscriptionStarted  解析服务端返回的start事件的json字符串内容
     *
     * @param string   $response  服务端返回的json字符串
     * @param NlsEvent $event     NLS事件参数对象
     *
     * @return array|bool 若成功, 返回所有事件的解析array, 同时若设置了回调会返回相关结果.
     *                    若失败, 返回false. 若成功但无事件, 返回true.
     */
    public function parseTranscriptionStarted($response, $event): array
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

            if (isset($this->_onTranscriptionStarted))
            {
                call_user_func(
                    array($this->_namespace. '\\' . $this->_classname, $this->_onTranscriptionStarted),
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
            $event->_result = $payload['result'];
            $event->_index = $payload['index'];
            $event->_time = $payload['time'];
            $event->_sentenceConfidence = $payload['confidence'];
            $event->_msg = $response;

            if (isset($this->_onTranscriptionResultChanged))
            {
                call_user_func(
                    array($this->_namespace. '\\' . $this->_classname, $this->_onTranscriptionResultChanged),
                    $event);
            }

            $arrayObj = array(
                'status_code' => $event->_statusCode,
                'status_text' => $event->_statusText,
                'task_id'     => $event->_taskId,
                'message_id'  => $event->_message_id,
                'event_name'  => $event->_msgType,
                'index'       => $event->_index,
                'time'        => $event->_time,
                'confidence'  => $event->_sentenceConfidence,
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
     * parseSentenceBegin  解析服务端返回的一句话开头事件的json字符串内容
     *
     * @param string   $response  服务端返回的json字符串
     * @param NlsEvent $event     NLS事件参数对象
     *
     * @return array|bool 若成功, 返回所有事件的解析array, 同时若设置了回调会返回相关结果.
     *                    若失败, 返回false. 若成功但无事件, 返回true.
     */
    public function parseSentenceBegin($response, $event): array
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
            $event->_index = $payload['index'];
            $event->_time = $payload['time'];
            $event->_msg = $response;

            if (isset($this->_onSentenceBegin))
            {
                call_user_func(
                    array($this->_namespace. '\\' . $this->_classname, $this->_onSentenceBegin),
                    $event);
            }

            $arrayObj = array(
                'status_code' => $event->_statusCode,
                'status_text' => $event->_statusText,
                'task_id'     => $event->_taskId,
                'message_id'  => $event->_message_id,
                'event_name'  => $event->_msgType,
                'index'       => $event->_index,
                'time'        => $event->_time,
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
     * parseSentenceEnd  解析服务端返回的一句话结束事件的json字符串内容
     *
     * @param string   $response  服务端返回的json字符串
     * @param NlsEvent $event     NLS事件参数对象
     *
     * @return array|bool 若成功, 返回所有事件的解析array, 同时若设置了回调会返回相关结果.
     *                    若失败, 返回false. 若成功但无事件, 返回true.
     */
    public function parseSentenceEnd($response, $event): array
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
            $event->_msg = $response;
            $event->_index = $payload['index'];
            $event->_time = $payload['time'];
            $event->_result = $payload['result'];
            $event->_sentenceConfidence = $payload['confidence'];
            $event->_stashResultBeginTime = $payload['begin_time'];
            $event->_stashResultText = $payload['stash_result'];
            $event->_stashResultSentenceId = $payload['sentence_id'];

            if (isset($this->_onSentenceEnd))
            {
                call_user_func(
                    array($this->_namespace. '\\' . $this->_classname, $this->_onSentenceEnd),
                    $event);
            }

            $arrayObj = array(
                'status_code'  => $event->_statusCode,
                'status_text'  => $event->_statusText,
                'task_id'      => $event->_taskId,
                'message_id'   => $event->_message_id,
                'event_name'   => $event->_msgType,
                'index'        => $event->_index,
                'time'         => $event->_time,
                'result'       => $event->_result,
                'confidence'   => $event->_sentenceConfidence,
                'begin_time'   => $event->_stashResultBeginTime,
                'stash_result' => $event->_stashResultText,
                'sentence_id'  => $event->_stashResultSentenceId,
                'response'     => $event->_msg
            );
            return $arrayObj;
        }
        else
        {
            return false;
        }
    }
    /**
     * parseTaskFailed  解析服务端返回的识别失败事件的json字符串内容
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
     * parseTranscriptionCompleted  解析服务端返回的实时识别完成事件的json字符串内容
     *
     * @param string   $response  服务端返回的json字符串
     * @param NlsEvent $event     NLS事件参数对象
     *
     * @return array|bool 若成功, 返回所有事件的解析array, 同时若设置了回调会返回相关结果.
     *                    若失败, 返回false. 若成功但无事件, 返回true.
     */
    public function parseTranscriptionCompleted($response, $event): array
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

            if (isset($this->_onTranscriptionCompleted))
            {
                call_user_func(
                    array($this->_namespace. '\\' . $this->_classname, $this->_onTranscriptionCompleted),
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
     * parseOnChannelClosed  解析服务端返回的实时识别结束事件的json字符串内容
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
