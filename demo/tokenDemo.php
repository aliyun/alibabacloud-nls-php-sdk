<?php

use AlibabaCloud\NLS\Token\NlsToken;

class TokenDemo {
    /**
     * @param string[] $args
     * @return void
     */
    public static function main($args){
        self::register();

        if (empty($args[0])) {
            die("Please input akId.\n");
        }
        if (empty($args[1])) {
            die("Please input akSecret.\n");
        }
        $akId = $args[0];
        $akSecret = $args[1];
        $tokenClient = new NlsToken();
        $tokenClient->setAccessKeyId($akId);
        $tokenClient->setKeySecret($akSecret);
        // 若为国际站语音服务, 则需要使用国际站访问域名
        //$tokenClient->setDomain("nlsmeta.ap-southeast-1.aliyuncs.com");
        $tokenClient->applyNlsToken();
        printf("token api version: %s\n", $tokenClient->getApiVersion());
        printf("token domain: %s\n", $tokenClient->getDomain());
        printf("token id: %s\n", $tokenClient->getToken());
        printf("token expire time: %s\n", $tokenClient->getExpireTime());
    }

    public static function register(){
        $file = dirname(__FILE__) . \DIRECTORY_SEPARATOR . '..' . '/nls/token/autoload.php';
        require_once $file;
    }
}

require_once dirname(__FILE__) . \DIRECTORY_SEPARATOR . '..' . \DIRECTORY_SEPARATOR . '/vendor/autoload.php';
TokenDemo::main(array_slice($argv, 1));

?>
