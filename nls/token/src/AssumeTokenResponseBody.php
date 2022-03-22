<?php

namespace AlibabaCloud\NLS\Token;

use AlibabaCloud\Tea\Model;

class AssumeTokenResponseBody extends Model
{
    /**
     * @var string
     */
    public $userId;
    public $tokenId;
    public $expireTime;
    public $errMesg;

    public function validate()
    {
    }

    public function toMap()
    {
        $res = [];
        if (null !== $this->userId) {
            $res['UserId'] = $this->userId;
        }
        if (null !== $this->tokenId) {
            $res['TokenId'] = $this->tokenId;
        }
        if (null !== $this->expireTime) {
            $res['ExpireTime'] = $this->expireTime;
        }
        if (null !== $this->errMesg) {
            $res['ErrMsg'] = $this->errMesg;
        }
        return $res;
    }

    /**
     * @param array $map
     *
     * @return AssumeTokenResponseBody
     */
    public static function fromMap($map = [])
    {
        $model = new self();
        if (isset($map['body'])) {
            $tokenBody = $map['body'];
            if (isset($tokenBody['ErrMsg'])) {
                $model->errMesg = $tokenBody['ErrMsg'];
            }
            if (isset($tokenBody['Token'])) {
                $tokenInfo = $tokenBody['Token'];
                if (isset($tokenInfo['UserId'])) {
                    $model->userId = $tokenInfo['UserId'];
                }
                if (isset($tokenInfo['Id'])) {
                    $model->tokenId = $tokenInfo['Id'];
                }
                if (isset($tokenInfo['ExpireTime'])) {
                    $model->expireTime = $tokenInfo['ExpireTime'];
                }
            } else {
                $model->errMesg = $map['body'];
            }
        }

        return $model;
    }
}
