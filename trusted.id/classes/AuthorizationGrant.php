<?php

namespace Trusted\Id;

require_once __DIR__ . '/../config.php';
require_once TR_ID_MODULE_PATH . '/classes/Utils.php';

session_start();

class AuthorizationGrant {

    protected $client_id = TR_ID_OPT_CLIENT_ID;
    protected $client_secret = TR_ID_OPT_CLIENT_SECRET;
    protected $redirect_uri = TR_ID_REDIRECT_URI;
    protected $grant_type = 'authorization_code';
    protected $code;

    function setCode($code) {
        $this->code = $code;
    }

    function getCode() {
        return $this->code;
    }

    function getGrantType() {
        return $this->grant_type;
    }

    function getRedirectUri() {
        return $this->redirect_uri;
    }

    function getClientSecret() {
        return $this->client_secret;
    }

    function getClientId() {
        return $this->client_id;
    }

    public function jsonSerialize() {
        $res = array(
            'client_id' => $this->client_id,
            'client_secret' => $this->client_secret,
            'redirect_uri' => $this->redirect_uri,
            'grant_type' => $this->grant_type,
            'code' => $this->code
        );
        return $res;
    }

    static function fromArray($array) {
        $res = new AuthorizationGrant();
        foreach ($res as $key => &$value) {
            $val = $array[$key];
            if (isset($val)) {
                $value = $val;
            }
        }
        return $res;
    }

}

