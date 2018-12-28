<?php

namespace Trusted\Id;

require_once __DIR__ . '/../config.php';
require_once TR_ID_MODULE_PATH . '/classes/Custom.php';
require_once TR_ID_MODULE_PATH . '/classes/Utils.php';

session_start();

/**
 * Description of oath2
 *
 * @author msu
 */
class OAuth2 {

    /**
     * Access token value
     * @var type
     */
    protected $access_token = null;

    /**
     * Refresh token value
     * @var type
     */
    protected $refresh_token = null;

    /**
     * Token type
     * @var type
     */
    protected $token_type = null;

    /**
     * Token lifetime in seconds
     */
    protected $expires_in = null;

    /**
     * Token scope
     * @var type
     */
    protected $scope = null;

    /**
     * User
     * @var type
     */
    protected $user = null;

    /**
     * Returns token user
     * @return \TUser
     */
    function getUser() {
        if (!$this->user) {
            $array = TAuthCommand::getUserProfileByToken($this->access_token);
            // TODO: Error when user is not found
            $user = TUser::fromArray($array);
            $this->setUser($user);
            $this->putToSession();
        }
        // print_r($array);
        return $this->user;
    }

    /**
     * Drops token from session
     */
    static function remove() {
        if (isset($_SESSION['TRUSTEDNET']['OAUTH'])) {
            unset($_SESSION['TRUSTEDNET']['OAUTH']);
        }
    }

    /**
     * Sets user data
     * @param type $user
     */
    function setUser($user) {
        $this->user = $user;
        $this->putToSession();
    }

    /**
     * Returns access token
     * @return type
     */
    function getAccessToken() {
        return $this->access_token;
    }

    /**
     * Returns Refresh token
     * @return type
     */
    function getRefreshToken() {
        return $this->refresh_token;
    }

    /**
     * Returns token type
     * @return type
     */
    function getType() {
        return $this->token_type;
    }

    /**
     * Token lifetime in seconds
     * @return type
     */
    function getExpiresIn() {
        return $this->expires_in;
    }

    /**
     * Return token scope
     * @return type
     */
    function getScope() {
        return $this->scope;
    }

    function hasExpired() {
        $expires = $this->expires_in;
        if (empty($expires)) {
            throw new RuntimeException('"expires" is not set on the token');
        }
        return $expires < time();
    }

    static function fromArray($array) {
        $res = new OAuth2();
        foreach ($res as $key => &$value) {
            $val = $array[$key];
            if (isset($val)) {
                $value = $val;
            }
        }
        $res->putToSession();
        return $res;
    }

    function checkToken() {
        return TAuthCommand::checkTokenExpiration($this->access_token);
    }

    /**
     * Receives new access token by refresh token
     * @return boolean
     */
    function refresh() {
        $res = false;
        $result = TAuthCommand::getAccessTokenByRefreshToken($this->refresh_token);
        if ($result) {
            foreach ($this as $key => &$value) {
                $val = $result[$key];
                if (isset($val)) {
                    $value = $val;
                }
            }
            $this->putToSession();
            $res = true;
        }
        return $res;
    }

    protected function putToSession() {
        $_SESSION['TRUSTEDNET']['OAUTH'] = serialize($this);
    }

    /**
     * Returns OAuth2 from current session
     * @return \OAuth2
     */
    static function getFromSession() {
        $res = null;
        if (isset($_SESSION['TRUSTEDNET']['OAUTH'])) {
            $res = unserialize($_SESSION['TRUSTEDNET']['OAUTH']);
            if (!$res->checkToken()) {
                Utils::debug('Access token refreshed');
                try {
                    $res->refresh();
                } catch (OAuth2Exception $e) {
                    Id\Custom::onOAuth2Exception($e);
                }
            }
        }
        return $res;
    }

}

// TODO: remove or move to separate file
class OAuth2Exception extends \Exception {
}

