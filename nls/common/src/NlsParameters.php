<?php

namespace AlibabaCloud\NLS\Common;

define("SDK_LANGUAGE", "php");
define("SDK_NAME"    , "nls-sdk-php");
define("SDK_VERSION" , "0.1.0");

class NlsParameters
{
    public function __construct()
    {
    }
    
    public static function generateUuid()
    {
        $taskId = md5("php-task-id" . uniqid(md5(microtime(true)), true));
        return $taskId;
    }

    public static function generateRequestHeader($appKey, $name, $taskId)
    {
        $messageId = md5("php-message-id" . uniqid(md5(microtime(true)), true));
        $headers['appkey'] = $appKey;
        $headers['name'] = $name;
        $headers['message_id'] = $messageId;
        $headers['task_id'] = $taskId;
        if ($name === 'StartRecognition' || $name === 'StopRecognition')
        {
            $namespace = 'SpeechRecognizer';
        }
        else if ($name === 'StartTranscription' || $name === 'StopTranscription')
        {
            $namespace = 'SpeechTranscriber';
        }
        else if ($name === 'StartSynthesis')
        {
            $namespace = 'SpeechSynthesizer';
        }
        $headers['namespace'] = $namespace;

        return $headers;
    }

    public static function generateRequestContext()
    {
        $contextSdk['language'] = SDK_LANGUAGE;
        $contextSdk['name'] = SDK_NAME;
        $contextSdk['version'] = SDK_VERSION;
        $context['sdk'] = $contextSdk;

        return $context;
    }
}

?>
