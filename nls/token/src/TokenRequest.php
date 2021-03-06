<?php

namespace AlibabaCloud\NLS\Token;

use AlibabaCloud\Credentials\Credential;
use AlibabaCloud\Credentials\Credential\Config;
use AlibabaCloud\OpenApiUtil\OpenApiUtilClient;
use AlibabaCloud\Tea\Exception\TeaError;
use AlibabaCloud\Tea\Exception\TeaUnableRetryError;
use AlibabaCloud\Tea\Request;
use AlibabaCloud\Tea\Tea;
use AlibabaCloud\Tea\Utils\Utils;
use AlibabaCloud\Tea\Utils\Utils\RuntimeOptions;
use Darabonba\OpenApi\Models\OpenApiRequest;
use Exception;

class TokenRequest
{
    protected $_endpoint;
    protected $_regionId;
    protected $_protocol;
    protected $_method;
    protected $_userAgent;
    protected $_endpointRule;
    protected $_endpointMap;
    protected $_suffix;
    protected $_readTimeout;
    protected $_connectTimeout;
    protected $_httpProxy;
    protected $_httpsProxy;
    protected $_socks5Proxy;
    protected $_socks5NetWork;
    protected $_noProxy;
    protected $_network;
    protected $_productId;
    protected $_maxIdleConns;
    protected $_endpointType;
    protected $_openPlatformEndpoint;
    protected $_credential;
    protected $_signatureVersion;
    protected $_signatureAlgorithm;
    protected $_headers;
    protected $_spi;

    /**
     * Init client with Config.
     *
     * @param config config contains the necessary information to create a client
     */
    public function __construct($config)
    {
        if (Utils::isUnset($config)) {
            throw new TeaError(['code' => 'ParameterMissing', 'message' => "'config' can not be unset"]);
        }
        if (!Utils::empty_($config->accessKeyId) && !Utils::empty_($config->accessKeySecret)) {
            if (!Utils::empty_($config->securityToken)) {
                $config->type = 'sts';
            } else {
                $config->type = 'access_key';
            }
            $credentialConfig = new Config([
                'accessKeyId' => $config->accessKeyId,
                'type' => $config->type,
                'accessKeySecret' => $config->accessKeySecret,
                'securityToken' => $config->securityToken,
            ]);
            $this->_credential = new Credential($credentialConfig);
        } elseif (!Utils::isUnset($config->credential)) {
            $this->_credential = $config->credential;
        }
        $this->_endpoint = $config->endpoint;
        $this->_endpointType = $config->endpointType;
        $this->_network = $config->network;
        $this->_suffix = $config->suffix;
        $this->_protocol = $config->protocol;
        $this->_method = $config->method;
        $this->_regionId = $config->regionId;
        $this->_userAgent = $config->userAgent;
        $this->_readTimeout = $config->readTimeout;
        $this->_connectTimeout = $config->connectTimeout;
        $this->_httpProxy = $config->httpProxy;
        $this->_httpsProxy = $config->httpsProxy;
        $this->_noProxy = $config->noProxy;
        $this->_socks5Proxy = $config->socks5Proxy;
        $this->_socks5NetWork = $config->socks5NetWork;
        $this->_maxIdleConns = $config->maxIdleConns;
        $this->_signatureVersion = $config->signatureVersion;
        $this->_signatureAlgorithm = $config->signatureAlgorithm;
    }

    /**
     * Encapsulate the request and invoke the network.
     *
     * @param string         $action   api name
     * @param string         $version  product version
     * @param string         $protocol http or https
     * @param string         $method   e.g. GET
     * @param string         $authType authorization type e.g. AK
     * @param string         $bodyType response body type e.g. String
     * @param OpenApiRequest $request  object of OpenApiRequest
     * @param RuntimeOptions $runtime  which controls some details of call api, such as retry times
     *
     * @return array the response
     *
     * @throws TeaError
     * @throws Exception
     * @throws TeaUnableRetryError
     */
    public function doTokenRequest($action, $version, $protocol, $method, $authType, $bodyType, $request, $runtime)
    {
        $request->validate();
        $runtime->validate();
        $_runtime = [
            'timeouted' => 'retry',
            'readTimeout' => Utils::defaultNumber($runtime->readTimeout, $this->_readTimeout),
            'connectTimeout' => Utils::defaultNumber($runtime->connectTimeout, $this->_connectTimeout),
            'httpProxy' => Utils::defaultString($runtime->httpProxy, $this->_httpProxy),
            'httpsProxy' => Utils::defaultString($runtime->httpsProxy, $this->_httpsProxy),
            'noProxy' => Utils::defaultString($runtime->noProxy, $this->_noProxy),
            'socks5Proxy' => Utils::defaultString($runtime->socks5Proxy, $this->_socks5Proxy),
            'socks5NetWork' => Utils::defaultString($runtime->socks5NetWork, $this->_socks5NetWork),
            'maxIdleConns' => Utils::defaultNumber($runtime->maxIdleConns, $this->_maxIdleConns),
            'retry' => [
                'retryable' => $runtime->autoretry,
                'maxAttempts' => Utils::defaultNumber($runtime->maxAttempts, 3),
            ],
            'backoff' => [
                'policy' => Utils::defaultString($runtime->backoffPolicy, 'no'),
                'period' => Utils::defaultNumber($runtime->backoffPeriod, 1),
            ],
            'ignoreSSL' => $runtime->ignoreSSL,
        ];
        $_lastRequest = null;
        $_lastException = null;

        try {
            $_request = new Request();
            $_request->protocol = Utils::defaultString($this->_protocol, $protocol);
            $_request->method = $method;
            $_request->pathname = '/';
            $_request->query = Tea::merge([
                'Action' => $action,
                'Format' => 'json',
                'Version' => $version,
                'Timestamp' => OpenApiUtilClient::getTimestamp(),
                'SignatureNonce' => Utils::getNonce(),
            ], $request->query);
            $headers = $this->getRpcHeaders();
            if (Utils::isUnset($headers)) {
                // endpoint is setted in product client
                $_request->headers = [
                    'host' => $this->_endpoint,
                    'x-acs-version' => $version,
                    'x-acs-action' => $action,
                    'user-agent' => $this->getUserAgent(),
                ];
            }
            if (!Utils::isUnset($request->body)) {
                $m = Utils::assertAsMap($request->body);
                $tmp = Utils::anyifyMapValue(OpenApiUtilClient::query($m));
                $_request->body = Utils::toFormString($tmp);
                $_request->headers['content-type'] = 'application/x-www-form-urlencoded';
            }

            $accessKeyId = $this->getAccessKeyId();
            $accessKeySecret = $this->getAccessKeySecret();
            $securityToken = $this->getSecurityToken();
            if (!Utils::empty_($securityToken)) {
                $_request->query['SecurityToken'] = $securityToken;
            }
            $_request->query['SignatureMethod'] = 'HMAC-SHA1';
            $_request->query['SignatureVersion'] = '1.0';
            $_request->query['AccessKeyId'] = $accessKeyId;
            $t = null;
            if (!Utils::isUnset($request->body)) {
                $t = Utils::assertAsMap($request->body);
            }
            $signedParam = Tea::merge($_request->query, OpenApiUtilClient::query($t));
            $_request->query['Signature'] = OpenApiUtilClient::getRPCSignature($signedParam, $_request->method, $accessKeySecret);

            $_lastRequest = $_request;
            $_response = Tea::send($_request, $_runtime);
            //printf("statusCode: %s \n", $_response->statusCode);
            //printf("resp body %s\n", $_response->body);

            if ($_response->statusCode == 200) {
                // success here
            } else {
            }
            $obj = Utils::readAsJSON($_response->body);
            $res = Utils::assertAsMap($obj);

            return [
                // 'headers' => $_response->headers,
                'body' => $res,
            ];
        } catch (Exception $e) {
            if (!($e instanceof TeaError)) {
                $e = new TeaError([], $e->getMessage(), $e->getCode(), $e);
            }
            throw $e;
        }

        throw new TeaUnableRetryError($_lastRequest, $_lastException);
    }

    /**
     * Get user agent.
     *
     * @return string user agent
     */
    public function getUserAgent()
    {
        $userAgent = Utils::getUserAgent($this->_userAgent);

        return $userAgent;
    }

    /**
     * Get accesskey id by using credential.
     *
     * @return string accesskey id
     */
    public function getAccessKeyId()
    {
        if (Utils::isUnset($this->_credential)) {
            return '';
        }
        $accessKeyId = $this->_credential->getAccessKeyId();
        return $accessKeyId;
    }

    /**
     * Get accesskey secret by using credential.
     *
     * @return string accesskey secret
     */
    public function getAccessKeySecret()
    {
        if (Utils::isUnset($this->_credential)) {
            return '';
        }
        $secret = $this->_credential->getAccessKeySecret();
        return $secret;
    }

    /**
     * Get security token by using credential.
     *
     * @return string security token
     */
    public function getSecurityToken()
    {
        if (Utils::isUnset($this->_credential)) {
            return '';
        }
        $token = $this->_credential->getSecurityToken();
        return $token;
    }

    /**
     * get RPC header for debug.
     *
     * @return array
     */
    public function getRpcHeaders()
    {
        $headers = $this->_headers;
        $this->_headers = null;

        return $headers;
    }
}
