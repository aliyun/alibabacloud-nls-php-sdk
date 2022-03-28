<?php

namespace AlibabaCloud\NLS\Common;

class NlsEvent
{
    public function __construct()
    {
    }

    public $_statusCode;
    public $_statusText;
    public $_msg;
    public $_msgType;
    public $_taskId;
    public $_messageId;
    public $_result;
    public $_displayText;
    public $_spokenText;
    public $_duration;

    public $_sentenceTimeoutStatus;
    public $_sentenceIndex;
    public $_sentenceTime;
    public $_sentenceBeginTime;
    public $_sentenceConfidence;

    public $_wakeWordAccepted;
    public $_wakeWordKnown;
    public $_wakeWordUserId;
    public $_wakeWordGender;

    public $_index;
    public $_time;
    public $_stashResultSentenceId;
    public $_stashResultBeginTime;
    public $_stashResultCurrentTime;
    public $_stashResultText;

    public $_subtitles;
    public $_data;
    public $_dataSize;
}

?>