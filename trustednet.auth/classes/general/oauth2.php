<?php

require_once __DIR__ . '/../config.php';
require_once TRUSTED_MODULE_PATH . "/classes/custom.php";
require_once TRUSTED_MODULE_PATH . "/classes/util.php";

session_start();

//$DBASE = null;
//
//if (TRUSTED_DB) {
//    $DBASE = new TDataBase();
//    $r = $DBASE->Connect(TRUSTED_DB_HOST, TRUSTED_DB_NAME, TRUSTED_DB_LOGIN, TRUSTED_DB_PASSWORD);
//}

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

    static function getBitrixAndTnUsers ($by, $order, $filter) {
        $find_id = (string)$filter["ID"];
        $find_login = (string)$filter["LOGIN"];
        $find_name = (string)$filter["NAME"];
        $find_email = (string)$filter["EMAIL"];
        $find_tn_id = (string)$filter["TN_ID"];
        $find_tn_giv_name = (string)$filter["TN_GIV_NAME"];
        $find_tn_fam_name = (string)$filter["TN_FAM_NAME"];
        $find_tn_email = (string)$filter["TN_EMAIL"];

        $sqlWhere = array();
        if ($find_id !== "") {
            $sqlWhere[] = "BU.ID = '" . $find_id . "'";
        }
        if ($find_login !== "") {
            $sqlWhere[] = "BU.LOGIN LIKE '%" . $find_login . "%'";
        }
        if ($find_name !== "") {
            $sqlWhere[] = "BU.NAME LIKE '%" . $find_login . "%'";
        }
        if ($find_email !== "") {
            $sqlWhere[] = "BU.EMAIL LIKE '%" . $find_email . "%'";
        }
        if ($find_tn_id !== "") {
            $sqlWhere[] = "TU.ID = '" . $find_tn_id . "'";
        }
        if ($find_tn_giv_name !== "") {
            $sqlWhere[] = "TU.GIVEN_NAME LIKE '%" . $find_tn_giv_name . "%'";
        }
        if ($find_tn_fam_name !== "") {
            $sqlWhere[] = "TU.FAMILY_NAME LIKE '%" . $find_tn_fam_name . "%'";
        }
        if ($find_tn_email !== "") {
            $sqlWhere[] = "TU.EMAIL LIKE '%" . $find_tn_email . "%'";
        }

        global $DB;
        $sql = "
            SELECT
                BU.ID, BU.LOGIN, CONCAT(BU.NAME, ' ', BU.LAST_NAME) AS NAME, BU.EMAIL,
                TU.ID as TN_ID, TU.GIVEN_NAME as TN_GIV_NAME, TU.FAMILY_NAME as TN_FAM_NAME, TU.EMAIL as TN_EMAIL
            FROM
                b_user as BU
            LEFT JOIN
                trn_user as TU
            ON
                BU.ID=TU.USER_ID";

        // Filtering
        if (count($sqlWhere)) {
            $sql .= " WHERE " . implode(" AND ", $sqlWhere);
        }

        // Ordering
        $fields = array(
            "ID" => "BU.ID",
            "LOGIN" => "BU.LOGIN",
            "NAME" => "CONCAT(BU.NAME, ' ', BU.LAST_NAME)",
            "EMAIL" => "BU.EMAIL",
            "TN_ID" => "TU.ID",
            "TN_GIV_NAME" => "TU.GIVEN_NAME",
            "TN_FAM_NAME" => "TU.FAMILY_NAME",
            "TN_EMAIL" => "TU.EMAIL",
        );
        $by = strtoupper($by);
        $order = strtoupper($order);
        if (array_key_exists($by, $fields)) {
            if ($order != "DESC") {
                $order = "ASC";
            }
            $sql .= " ORDER BY " . $fields[$by] . " " . $order . ";";
        }
        $rows = $DB->Query($sql);
        return $rows;
    }

    /**
     * Returns DB user
     * @param type $user
     * @return type
     */
    static function getUser($user) {
        return TDataBaseUser::getUserById($user->getId());
    }

    /**
     * Returns DB user by id
     * @global type $DBASE
     * @param type $id
     * @return type
     */
    static function getUserById($id) {
        global $DB;
        $sql = "SELECT * FROM trn_user WHERE ID = " . $id;
        $res = null;
        $rows = $DB->Query($sql);
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
        global $DB;
        $sql = "SELECT * FROM trn_user WHERE USER_ID = " . $userId;
        $res = null;
        $rows = $DB->Query($sql);
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
            global $DB;
            //Save
            debug('Save User to DataBase');
            $userId = "NULL";
            if ($user->getUserId()) {
                $userId = "'" . $user->getUserId() . "'";
            }
            $familyName = "NULL";
            if ($user->getFamilyName()) {
                $familyName = "'" . $user->getFamilyName() . "'";
            }
            $givenName = "NULL";
            if ($user->getGivenName()) {
                $givenName = "'" . $user->getGivenName() . "'";
            }
            $email = "NULL";
            if ($user->getEmail()) {
                $email = "'" . $user->getEmail() . "'";
            }
            $sql = "UPDATE " . TRUSTEDNET_DB_TABLE_USER . " SET "
                    . "USER_ID = " . $userId . ", "
                    . "FAMILY_NAME = " . $familyName . ", "
                    . "GIVEN_NAME = " . $givenName . ", "
                    . "EMAIL = " . $email . " "
                    . "WHERE ID = " . $user->getId();
            $DB->Query($sql);
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
        global $DB;
        debug('Insert User to DataBase');
        $userId = "NULL";
        if ($user->getUserId()) {
            $userId = "'" . $user->getUserId() . "'";
        }
        $familyName = "NULL";
        if ($user->getFamilyName()) {
            $familyName = "'" . $user->getFamilyName() . "'";
        }
        $givenName = "NULL";
        if ($user->getGivenName()) {
            $givenName = "'" . $user->getGivenName() . "'";
        }
        $email = "NULL";
        if ($user->getEmail()) {
            $email = "'" . $user->getEmail() . "'";
        }
        $sql = "INSERT INTO " .
                    TRUSTEDNET_DB_TABLE_USER . " (ID, USER_ID, FAMILY_NAME, GIVEN_NAME, EMAIL)
                VALUES ("
                    . $user->getId() . ", "
                    . $userId . ", "
                    . $familyName . ", "
                    . $givenName . ", "
                    . $email
                    . ")";
        $DB->Query($sql);
    }

    static function removeUserById($id) {
        global $DB;
        debug('removeUserById');
        $sql = "DELETE FROM " . TRUSTEDNET_DB_TABLE_USER . " WHERE "
                . "ID = " . $id;
        debug('SQL: ', $sql);
        $DB->Query($sql);
    }

    static function removeUserByUserId($userId) {
        global $DB;
        debug('removeUserByUserId');
        $sql = "DELETE FROM " . TRUSTEDNET_DB_TABLE_USER . " WHERE "
                . "USER_ID = " . $userId;
        debug('SQL: ', $sql);
        $DB->Query($sql);
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

    function getUserId() {
        return $this->userId;
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
        if (!$res->avatarUrl) {
            $res->avatarUrl = TRUSTED_COMMAND_URI_HOST . "/static/new/img/ava.jpg";
        }
        return $res;
    }

}

class TUser {

    protected $id;
    protected $userId;
    protected $familyName;
    protected $givenName;
    protected $email;
    protected $serviceUser = null;

    function getId() {
        return $this->id;
    }

    function setId($id) {
        $this->id = $id;
        $this->serviceUser = null;
    }

    function getUserId() {
        return $this->userId;
    }

    function setUserId($userId) {
        $this->userId = $userId;
    }

    function getFamilyName() {
        return $this->familyName;
    }

    function setFamilyName($familyName) {
        $this->familyName = $familyName;
    }

    function getGivenName() {
        return $this->givenName;
    }

    function setGivenName($givenName) {
        $this->givenName = $givenName;
    }

    function getEmail() {
        return $this->email;
    }

    function setEmail($email) {
        $this->email = $email;
    }

    /**
     * Returns user parameters received from service
     * @return \ServiceUser
     * @throws OAuth2Exception
     */
    function getServiceUser() {
        $res = $this->serviceUser;
        if (!$res && $this->id) {
            $token = OAuth2::getFromSession();
            if ($token) {
                $arUser = TAuthCommand::getUserProfileByToken($token->getAccessToken());
                //Check users ids
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
        $this->userId = $serviceUser->getUserId();
        $this->familyName = $serviceUser->getFamilyName();
        $this->givenName = $serviceUser->getGivenName();
        $this->email = $serviceUser->getEmail();
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

    static function checkTokenExpiration($accessToken) {
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

    static function getAppParameters($accessToken, $controller) {
        $res = false;
        switch ($controller) {
            case "login":
                $url = TRUSTED_COMMAND_REST_LOGIN;
                break;
            case "social":
                $url = TRUSTED_COMMAND_REST_SOCIAL;
                break;
            case "certificate":
                $url = TRUSTED_COMMAND_REST_CERTIFICATE;
                break;
            default:
                return $res;
        }
        if ($accessToken) {
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_HTTPHEADER, array('Authorization: Bearer ' . $accessToken));
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($curl, CURLOPT_POSTFIELDS, "clientId=" . TRUSTED_LOGIN_CLIENT_ID);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($curl);
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
        }
        return $res;
    }

    static function getAppList($accessToken) {
        $res = false;
        if ($accessToken) {
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_HTTPHEADER, array('Authorization: Bearer ' . $accessToken));
            curl_setopt($curl, CURLOPT_URL, TRUSTED_COMMAND_REST_APP_LIST);
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($curl);
            if (!curl_errno($curl)) {
                $info = curl_getinfo($curl);
                if ($info['http_code'] == 200) {
                    $res = json_decode($response, true);
                    $res = $res["list"];
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
        }
        return $res;
    }

    // SearchField can be:
    // entitityId, username, email, displayName, familyName, givenName, login, id
    static function pullTnInfo($accessToken, $searchField, $searchTerm) {
        $res = false;
        if ($accessToken) {
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                'Authorization: Bearer ' . $accessToken,
                'Content-Type: application/x-www-form-urlencoded',
            ));
            curl_setopt($curl, CURLOPT_URL, "https://net.trusted.ru/trustedapp/rest/user/find");
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($curl, CURLOPT_POSTFIELDS, "t=" . $searchTerm);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($curl);
            if (!curl_errno($curl)) {
                $info = curl_getinfo($curl);
                if ($info['http_code'] == 200) {
                    $responseList = json_decode($response, true);
                    $responseList = $responseList["users"]["list"];
                    foreach ($responseList as $user) {
                        if ($user[$searchField] == $searchTerm) {
                            $res = $user;
                        }
                    }
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
        }
        return $res;
    }
}

class OAuth2Exception extends Exception {
}

