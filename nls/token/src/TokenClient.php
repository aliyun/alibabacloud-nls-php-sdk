<?php

namespace AlibabaCloud\NLS\Token;

use AlibabaCloud\SDK\Sts\V20150401\Models\AssumeRoleRequest;
use AlibabaCloud\Tea\Utils\Utils;
use AlibabaCloud\Tea\Utils\Utils\RuntimeOptions;
use Darabonba\OpenApi\Models\OpenApiRequest;

use AlibabaCloud\NLS\Token\TokenRequest;

class TokenClient extends TokenRequest
{
    public function __construct($config)
    {
        parent::__construct($config);
        $this->_endpointRule = 'regional';
        $this->_endpoint = $config->endpoint;
        $this->_regionId = $config->regionId;
        $this->_endpoint = $config->endpoint;
        $this->_signatureVersion = $config->signatureVersion;
    }

    /**
     * @param AssumeRoleRequest $request
     * @param RuntimeOptions    $runtime
     *
     * @return AssumeTokenResponse
     */
    public function assumeTokenWithOptions($request, $runtime)
    {
        Utils::validateModel($request);
        $req = new OpenApiRequest([
            'body' => Utils::toMap($request),
        ]);

        return AssumeTokenResponseBody::fromMap(
            $this->doTokenRequest(
                'CreateToken', $this->_signatureVersion, 'HTTPS', 'POST', 'AK', 'JSON', $req, $runtime));
    }

    /**
     * @param AssumeRoleRequest $request
     *
     * @return AssumeTokenResponse
     */
    public function assumeToken($request)
    {
        $runtime = new RuntimeOptions([]);

        return $this->assumeTokenWithOptions($request, $runtime);
    }
}
