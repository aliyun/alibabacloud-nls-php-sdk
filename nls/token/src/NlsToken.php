<?php

namespace AlibabaCloud\NLS\Token;

use AlibabaCloud\Tea\Tea;
use AlibabaCloud\Tea\Utils\Utils;
use AlibabaCloud\Tea\Console\Console;
use Darabonba\OpenApi\Models\Config;
use AlibabaCloud\SDK\Sts\V20150401\Models\AssumeRoleRequest;

use AlibabaCloud\NLS\Token\TokenClient;


/**
 * 创建访问令牌token的请求
 */
class NlsToken {
    protected $_domain;
    protected $_protocol;
    protected $_method;
    protected $_serverVersion;
    protected $_serverResourcePath;
    protected $_accessKeyId;
    protected $_accessKeySecret;
    protected $_regionId;
    protected $_action;
    protected $_errorMsg;
    protected $_tokenId;
    protected $_expireTime;

    /**
     * NlsToken 的构造函数
     *
     * @return NlsToken
     */
    public function __construct()
    {
        $this->_domain = 'nls-meta.cn-shanghai.aliyuncs.com';
        $this->_protocol = 'HTTPS';
        $this->_serverVersion = '2019-02-28';
        $this->_serverResourcePath = '/pop/2019-02-28/tokens';
        $this->_regionId = 'cn-shanghai';
        $this->_action = 'CreateToken';
        $this->_method = 'POST';
    }

    /**
     * paramCheck  检查各参数的有效性.
     *
     * @return bool  若参数失败, 返回false, 若参数有效, 返回true.
     */
    public function paramCheck()
    {
        if (empty($this->_accessKeySecret))
        {
            $this->_errorMsg = "AccessKeySecret is empty.";
            return false;
        }

        if (empty($this->_accessKeyId))
        {
            $this->_errorMsg = "AccessKeyId is empty.";
            return false;
        }

        if (empty($this->_domain))
        {
            $this->_errorMsg = "Domain is empty.";
            return false;
        }

        if (empty($this->_serverVersion))
        {
            $this->_errorMsg = "ServerVersion is empty.";
            return false;
        }

        if (empty($this->_serverResourcePath))
        {
            $this->_errorMsg = "ServerResourcePath is empty.";
            return false;
        }

        if (empty($this->_action))
        {
            $this->_errorMsg = "Action is empty.";
            return false;
        }

        if (empty($this->_regionId))
        {
            $this->_errorMsg = "RegionId is empty.";
            return false;
        }

        return true;
    }

    public function applyNlsToken()
    {
        if (self::paramCheck() === false)
        {
            printf("params check failed:%s\n", $this->_errorMsg);
            return false;
        }

        $config = new Config([
            // 您的AccessKey ID
            "accessKeyId" => $this->_accessKeyId,
            // 您的AccessKey Secret
            "accessKeySecret" => $this->_accessKeySecret
        ]);
        $config->endpoint = $this->_domain;
        $config->regionId = $this->_regionId;
        $config->signatureVersion = $this->_serverVersion;

        $client = new TokenClient($config);
        $assumeRoleRequest = new AssumeRoleRequest([]);
        $resp = $client->assumeToken($assumeRoleRequest);
        $this->_tokenId = $resp->tokenId;
        $this->_errorMsg = $resp->errMesg;
        $this->_expireTime = $resp->expireTime;
        if (empty($this->_tokenId))
        {
            Console::log(Utils::toJSONString(Tea::merge($resp)));
        }
    }

    /**
     * getErrorMsg  获得token的当前错误信息.
     *
     * @return string  错误信息
     */
    public function getErrorMsg()
    {
        $errorMsg = $this->_errorMsg;
        return $errorMsg;
    }

    /**
     * getToken  获得token id字符串.
     *
     * @return string  token id字符串
     */
    public function getToken()
    {
        $tokenId = $this->_tokenId;
        return $tokenId;
    }

    /**
     * getExpireTime  获得token超时时间戳.
     *
     * @return int  token的超时时间戳
     */
    public function getExpireTime()
    {
        $expireTime = $this->_expireTime;
        return $expireTime;
    }

    /**
     * getApiVersion  获得token调用的API版本.
     *
     * @return string  token调用的API版本
     */
    public function getApiVersion()
    {
        $apiVersion = $this->_serverVersion;
        return $apiVersion;
    }

    /**
     * getDomain  获得通用访问域名.
     *
     * @return string  通用访问域名
     */
    public function getDomain()
    {
        $domain = $this->_domain;
        return $domain;
    }


    /**
     * setKeySecret  设置访问密钥AccessKey Secret.
     *               详见:https://help.aliyun.com/document_detail/69835.htm?spm=a2c4g.11186623.0.0.6ec853987Ab1ha#topic-2572187
     * @param string $KeySecret  访问密钥AccessKey Secret字符串
     */
    public function setKeySecret($KeySecret)
    {
        $this->_accessKeySecret = $KeySecret;
    }

    /**
     * setAccessKeyId  设置访问密钥AccessKey ID.
     *                 详见:https://help.aliyun.com/document_detail/69835.htm?spm=a2c4g.11186623.0.0.6ec853987Ab1ha#topic-2572187
     * @param string $KeyId  访问密钥AccessKey ID字符串
     */
    public function setAccessKeyId($KeyId)
    {
        $this->_accessKeyId = $KeyId;
    }

    /**
     * setDomain  设置产品的通用访问域名, 此为固定值, 无特殊情况不需要调用
     *
     * @param string $domain  通用访问域名
     */
    public function setDomain($domain)
    {
        $this->_domain = $domain;

        if ($this->_domain === "nlsmeta.ap-southeast-1.aliyuncs.com")
        {
            $this->_regionId = "ap-southeast-1";
            $this->_serverVersion = "2019-07-17";
            $this->_serverResourcePath = '/pop/';
            $this->_serverResourcePath .= "2019-07-17";
            $this->_serverResourcePath .= '/tokens';
        }
    }

    /**
     * setServerVersion  设置API的版本号, 此为固定值, 无特殊情况不需要调用
     *                   若需调用, 建议在setDomain()后调用
     *
     * @param string $serverVersion  API的版本号
     */
    public function setServerVersion($serverVersion)
    {
        $this->_serverVersion = $serverVersion;
        $this->_serverResourcePath = '/pop/';
        $this->_serverResourcePath .= $serverVersion;
        $this->_serverResourcePath .= '/tokens';
    }

    /**
     * setServerResourcePath  设置API的版本号, 此为固定值, 无特殊情况不需要调用
     *
     * @param string $serverResourcePath  API的版本号
     */
    public function setServerResourcePath($serverResourcePath)
    {
        $this->_serverResourcePath = $serverResourcePath;
    }

    /**
     * setRegionId  设置服务端地域ID, 此为固定值, 无特殊情况不需要调用
     *              若需调用, 建议在setDomain()后调用
     *
     * @param string $regionId  服务的地域ID, 默认cn-shanghai
     */
    public function setRegionId($regionId)
    {
        $this->_regionId = $regionId;
    }

    /**
     * setAction  设置API的名称, 此为固定值, 无特殊情况不需要调用
     *
     * @param string $action  API的名称, 默认CreateToken
     */
    public function setAction($action)
    {
        $this->_action = $action;
    }
}

?>
