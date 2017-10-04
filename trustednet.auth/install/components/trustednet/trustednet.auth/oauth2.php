<?php

require_once (__DIR__ . '/config.php');
require_once TRUSTED_MODULE_DATA;
require_once TRUSTED_MODULE_AUTH_ROOT . "/custom.php";

session_start();

$DBASE = null;

if (TRUSTED_DB) {
    $DBASE = new TDataBase();
    $r = $DBASE->Connect(TRUSTED_DB_HOST, TRUSTED_DB_NAME, TRUSTED_DB_LOGIN, TRUSTED_DB_PASSWORD);
}

/**
 * Description of oath2
 *
 * @author msu
 */
class OAuth2 {

    /**
     * �������� ����� �������
     * @var type 
     */
    protected $access_token = null;

    /**
     * �������� ����� �������������
     * @var type 
     */
    protected $refresh_token = null;

    /**
     * ��� �����
     * @var type 
     */
    protected $token_type = null;

    /**
     * ����� ����� ����� ������� (� ��������)
     */
    protected $expires_in = null;

    /**
     * ���������� �����
     * @var type 
     */
    protected $scope = null;

    /**
     * ������������
     * @var type 
     */
    protected $user = null;

    /**
     * ���������� ������������ �����
     * @return \TUser
     */
    function getUser() {
        if (!$this->user) {
            $array = TAuthCommand::getUserProfileByToken($this->access_token);
            // TODO: ������ ���� ������������ ������ � ������������ �� ��������
            $user = TUser::fromArray($array);
            $this->setUser($user);
            $this->putToSession();
        }
        // print_r($array);
        return $this->user;
    }

    /**
     * ������� ���� �� �����
     */
    static function remove() {
        if (isset($_SESSION['TRUSTEDNET']['OAUTH'])) {
            unset($_SESSION['TRUSTEDNET']['OAUTH']);
        }
    }

    /**
     * ������ ������ � ������������
     * @param type $user
     */
    function setUser($user) {
        $this->user = $user;
        $this->putToSession();
    }

    /**
     * ���������� ���� �������
     * @return type
     */
    function getAccessToken() {
        return $this->access_token;
    }

    /**
     * ���������� ���� ��������������
     * @return type
     */
    function getRefreshToken() {
        return $this->refresh_token;
    }

    /**
     * ���������� ���
     * @return type
     */
    function getType() {
        return $this->token_type;
    }

    /**
     * ����� ����� ����� ������� (� ��������)
     * @return type
     */
    function getExpiresIn() {
        return $this->expires_in;
    }

    /**
     * ���������� ���������� �����
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

    /**
     * �������� ������������ ����� �������
     * @return type
     */
    function checkToken() {
        return TAuthCommand::checkTockenExpiration($this->access_token);
    }

    /**
     * ������� ����� ���� ������� �� ����� ��������������
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
     * ���������� OAuth2 �� ������� ������
     * @return \OAuth2
     */
    static function getFromSession() {
        $res = null;
        if (isset($_SESSION['TRUSTEDNET']['OAUTH'])) {
            $res = unserialize($_SESSION['TRUSTEDNET']['OAUTH']);
            if (!$res->checkToken()) {
                debug("Access token refreshed");
                try {
                    $res->refresh();
                } catch (OAuth2Exception $e) {
                    onOAuth2Exception($e);
                }
            }
        }
        return $res;
    }

}

class AuthorizationGrant {

    protected $client_id = TRUSTED_LOGIN_CLIENT_ID;
    protected $client_secret = TRUSTED_LOGIN_CLIENT_SECRET;
    protected $redirect_uri = TRUSTED_AUTH_REDIRECT_URI;
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
            "client_id" => $this->client_id,
            "client_secret" => $this->client_secret,
            "redirect_uri" => $this->redirect_uri,
            "grant_type" => $this->grant_type,
            "code" => $this->code
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

class TDataBaseUser {

    /**
     * ���������� ������������ ��
     * @param type $user
     * @return type
     */
    static function getUser($user) {
        return TDataBaseUser::getUserById($user->getId());
    }

    /**
     * ���������� ������������ �� �� id
     * @global type $DBASE
     * @param type $id
     * @return type
     */
    static function getUserById($id) {
        global $DBASE;
        $sql = "SELECT * FROM trn_user WHERE ID = " . $id;
        $res = null;
        $rows = $DBASE->Query($sql);
        if ($row = $rows->Fetch()) {
            $array = array(
                "id" => $row['ID'],
                "userId" => $row['USER_ID']
            );
            $res = TUser::fromArray($array);
        }
        return $res;
    }

    static function getUserByUserId($userId) {
        global $DBASE;
        $sql = "SELECT * FROM trn_user WHERE USER_ID = " . $userId;
        $res = null;
        $rows = $DBASE->Query($sql);
        if ($row = $rows->Fetch()) {
            $array = array(
                "id" => $row['ID'],
                "userId" => $row['USER_ID']
            );
            $res = TUser::fromArray($array);
        }
        return $res;
    }

    static function saveUser($user) {
        
        if (TDataBaseUser::getUser($user)) {
            global $DBASE;
            //Save
            debug('Save User to DataBase');
            $userId = $user->getUserId()? : 'NULL';
            $sql = "UPDATE " . TRUSTEDNET_DB_TABLE_USER . " SET "
                    . "USER_ID = " . $userId . " "
                    . "WHERE ID = " . $user->getId();
            $DBASE->Query($sql);
        } else {
            //Insert
            TDataBaseUser::insertUser($user);
        }
    }

    /**
     * 
     * @global type $DBASE
     * @param \TUser $user
     */
    static function insertUser($user) {
        global $DBASE;
        debug('Insert User to DataBase');
        $userId = $user->getUserId()? : 'NULL';
        $sql = "INSERT INTO " . TRUSTEDNET_DB_TABLE_USER . " (ID, USER_ID) VALUES ("
                . $user->getId() . ", "
                . $userId
                . ")";
        $DBASE->Query($sql);
    }

    static function removeUserById($id) {
        global $DBASE;
        debug('removeUserById');
        $sql = "DELETE FROM " . TRUSTEDNET_DB_TABLE_USER . " WHERE "
                . "ID = " . $id;
        debug('SQL: ', $sql);
        $DBASE->Query($sql);
    }

    static function removeUserByUserId($userId) {
        global $DBASE;
        debug('removeUserByUserId');
        $sql = "DELETE FROM " . TRUSTEDNET_DB_TABLE_USER . " WHERE "
                . "USER_ID = " . $userId;
        debug('SQL: ', $sql);
        $DBASE->Query($sql);
    }

    static function removeUser($user) {
        debug('removeUser');
        TDataBaseUser::removeUserById($user->getId());
    }

}

class ServiceUser {

    protected $_data;
    protected $id;
    protected $username;
    protected $email;
    protected $displayName;
    protected $additionalName;
    protected $familyName;
    protected $givenName;
    protected $avatarUrl;

    function toArray() {
        return $this->_data;
    }

    function getId() {
        return $this->id;
    }

    function getUsername() {
        return $this->username;
    }

    function getEmail() {
        return strtolower($this->email);
    }

    function getDisplayName() {
        return $this->displayName;
    }

    function getAdditionalName() {
        return $this->additionalName;
    }

    function getFamilyName() {
        return $this->familyName;
    }

    function getGivenName() {
        return $this->givenName;
    }

    function getAvatarUrl($accessToken) {
        return $this->avatarUrl . "?access_token=" . $accessToken;
    }

    static function fromArray($array) {
        $res = new ServiceUser();
        $res->_data = $array;
        foreach ($res as $key => &$value) {
            $val = $array[$key];
            if (isset($val)) {
                $value = $val;
            }
        }
        // get avatar
        $props = $array["properties"];
        foreach ($props as $key => &$value) {
            if ($value["type"] == "thumbnailUrl") {
                $res->avatarUrl = TRUSTED_COMMAND_REST . "/storage/" . $value["value"];
                break;
            }
        }
        if (!$res->avatarUrl){
            $res->avatarUrl = TRUSTED_COMMAND_URI_HOST . "/static/new/img/ava.jpg";
        }
        return $res;
    }

}

class TUser {

    protected $id;
    protected $userId;
    protected $serviceUser = null;

    function getId() {
        return $this->id;
    }

    function setId($id) {
        $this->id = $id;
        $this->serviceUser = null;
    }

    function setUserId($userId) {
        $this->userId = $userId;
    }

    function getUserId() {
        return $this->userId;
    }

    /**
     * ���������� ������ � ������������ ���������� � �������. 
     * @return \ServiceUser
     * @throws OAuth2Exception
     */
    function getServiceUser() {
        $res = $this->serviceUser;
        if (!$res && $this->id) {
            $token = OAuth2::getFromSession();
            if ($token) {
                $arUser = TAuthCommand::getUserProfileByToken($token->getAccessToken());
                //�������� ��������������� �������������
                //debug($arUser);
                if ($arUser['id'] == $this->id) {
                    $res = ServiceUser::fromArray($arUser);
                } else {
                    throw new OAuth2Exception(TRUSTEDNET_ERROR_MSG_DIFFERENT_USER_ID, TRUSTEDNET_ERROR_CODE_DIFFERENT_USER_ID, null);
                }
            } else {
                throw new OAuth2Exception(TRUSTEDNET_ERROR_MSG_TOKEN_NOT_FOUND, TRUSTEDNET_ERROR_CODE_TOKEN_NOT_FOUND, null);
            }
        }
        return $res;
    }

    function setServiceUser($serviceUser) {
        $this->serviceUser = $serviceUser;
        $this->id = $serviceUser->getId();
    }

    static function fromArray($array) {
        $res = new TUser();
        foreach ($res as $key => &$value) {
            $val = $array[$key];
            if (isset($val)) {
                $value = $val;
            }
        }
        return $res;
    }

    function save() {
        TDataBaseUser::saveUser($this);
    }

}

class TAuthCommand {

    static protected function getToken(&$curl, &$response) {
        $res = null;
        if (!curl_errno($curl)) {
            $info = curl_getinfo($curl);
            if ($info['http_code'] == 200) {
                $res = json_decode($response, true);
            } else {
                $message = "Wrong HTTP response status " . $info['http_code'];
                if ($response) {
                    $error = json_decode($response, true);
                    if ($error) {
                        $message .= PHP_EOL . $error["error"] . " - " . $error["error_description"];
                    }
                }
                debug("OAuth request error", $message);
                throw new OAuth2Exception($message, 0, null);
            }
        } else {
            $error = curl_error($curl);
            curl_close($curl);
            debug("CURL error", $error);
            throw new OAuth2Exception(TRUSTEDNET_ERROR_MSG_CURL, TRUSTEDNET_ERROR_CODE_CURL, null);
        }
        return $res;
    }

    static function getAccessTokenByCode($code) {
        debug("Run: getAccessTokenByCode");
        $AG = new AuthorizationGrant();
        $AG->setCode($code);
        $params = $AG->jsonSerialize();
        //$params["prompt"] = "login";
        $url = TRUSTED_COMMAND_URI_TOKEN;
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($curl, CURLOPT_USERPWD, $AG->getClientId() . ':' . $AG->getClientSecret());

        curl_setopt($curl, CURLOPT_URL, $url);
        debug("CURL url:", $url);
        curl_setopt($curl, CURLOPT_POST, true);
        $post_fields = urldecode(http_build_query($params));
        curl_setopt($curl, CURLOPT_POSTFIELDS, $post_fields);
        debug("CURL post fields:", $post_fields);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_SSLVERSION, TRUSTED_SSL_VERSION); 
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false); 
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); 
        $response = curl_exec($curl);
        $res = TAuthCommand::getToken($curl, $response);
        return $res;
    }

    static function getAccessTokenByRefreshToken($refresh_token) {
        $params = array(
            "grant_type" => "refresh_token",
            "refresh_token" => $refresh_token, //Refresh token from the approval step.
            "format" => json //Expected return format. This parameter is optional. The default is json. Values are: [urlencoded, json, xml]
        );
        $url = TRUSTED_COMMAND_URI_TOKEN;
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($curl, CURLOPT_USERPWD, TRUSTED_LOGIN_CLIENT_ID . ':' . TRUSTED_LOGIN_CLIENT_SECRET);

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, urldecode(http_build_query($params)));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSLVERSION, TRUSTED_SSL_VERSION); 
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($curl);
        $res = TAuthCommand::getToken($curl, $response);

        return $res;
    }

    static function getUserProfileByToken($accessToken) {
        /* �������� ������ � ������������ � ������� ������ */
        $response = false;
        if ($accessToken) {
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_HTTPHEADER, array('Authorization: Bearer ' . $accessToken));
            curl_setopt($curl, CURLOPT_URL, TRUSTED_COMMAND_URI_USERPROFILE);
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_SSLVERSION, TRUSTED_SSL_VERSION); 
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            $result = curl_exec($curl);
            if (!curl_errno($curl)) {
                $info = curl_getinfo($curl);
                if ($info['http_code'] == 200) {
                    $res = json_decode($response, true);
                } else {
                    $message = "Wrong HTTP response status " . $info['http_code'];
                    if ($response) {
                        $error = json_decode($response, true);
                        if ($error) {
                            $message .= PHP_EOL . $error["error"] . " - " . $error["error_description"];
                        }
                    }
                    debug("OAuth request error", $message);
                    throw new OAuth2Exception($message, 0, null);
                }
            }
            else{
                $error = curl_error($curl);
                curl_close($curl);
                debug("CURL error", $error);
                throw new OAuth2Exception(TRUSTEDNET_ERROR_MSG_CURL, TRUSTEDNET_ERROR_CODE_CURL, null);
            }
            curl_close($curl);
            // TODO: wrong $result
            $response = json_decode($result, true);
        }
        $res = null;
        if ($response["success"]) {
            $res = $response["user"];
        }
        return $res;
    }

    static function checkTockenExpiration($accessToken) {
        debug("access token", $accessToken);
        $res = false;
        if ($accessToken) {
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($curl, CURLOPT_USERPWD, TRUSTED_LOGIN_CLIENT_ID . ':' . TRUSTED_LOGIN_CLIENT_SECRET);
            curl_setopt($curl, CURLOPT_URL, TRUSTED_COMMAND_URI_CHECK_TOKEN . "?token=" . $accessToken);
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_SSLVERSION, TRUSTED_SSL_VERSION); 
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            $result = curl_exec($curl);
            if (!curl_errno($curl)) {
                $info = curl_getinfo($curl);
                curl_close($curl);
                if ($info['http_code'] == 200) {
                    $res = true;
                } else if ($info['http_code'] == 400) {
                    $res = false;
                } else {
                    $message = "Wrong HTTP response status " . $info['http_code'];
                    if ($result) {
                        $error = json_decode($result, true);
                        if ($error) {
                            $message .= PHP_EOL . $error["error"] . " - " . $error["error_description"];
                        }
                    }
                    debug("OAuth request error", $message);
                    throw new OAuth2Exception($message, 0, null);
                }
            } else {
                curl_close($curl);
                $error = curl_error($curl);
                debug("CURL error", $error);
                throw new OAuth2Exception(TRUSTEDNET_ERROR_MSG_CURL, TRUSTEDNET_ERROR_CODE_CURL, null);
            }
        }
        return $res;
    }

}

class OAuth2Exception extends Exception {
    
}
