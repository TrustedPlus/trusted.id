<?php

require_once __DIR__ . "/../config.php";
require_once __DIR__ . "/oauth2.php";

class TrustedAuth
{
    private $MODULE_ID = TN_AUTH_MODULE_ID;

    private $SERVICE_URL = "https://net.trusted.ru";
    private $SERVICE_REGISTER_URL;
    private $SERVICE_CODE_USER_EXISTS = 1501;

    private $CLIENT_ID = null;
    private $CLIENT_SECRET = null;

    private $USER_INFO_TEMPLATE_ID;
    private $USE_SEND_MAIL_SETTINGS = TN_USE_SEND_MAIL_SETTINGS;
    private $SHOULD_SEND_MAIL;

    private $ERROR_OCCURRED = false;
    private $ERROR_MESSAGE;

    function log($data, $level = LOG_LEVEL_DEBUG)
    {
        TN_DEBUG && AddMessage2Log($data);
    }

    function __construct()
    {
        define("LOG_LEVEL_ERROR", "ERROR");
        define("LOG_LEVEL_WARNING", "WARNING");
        define("LOG_LEVEL_INFO", "INFO");
        define("LOG_LEVEL_DEBUG", "DEBUG");

        $this->log("Constructor");
        $this->SERVICE_REGISTER_URL = $this->SERVICE_URL . "/idp/sso/user/appregister";

        // SET CLIENT ID
        $client_id = $this->getOption("CLIENT_ID");
        if ($client_id) {
            $this->CLIENT_ID = $client_id;
        } else {
            $this->setError(GetMessage("TN_ERROR_CLIENT_ID_NOT_SET"));
        }

        // SET CLIENT SECRET
        $client_secret = $this->getOption("CLIENT_SECRET");
        if ($client_secret) {
            $this->CLIENT_SECRET = $client_secret;
        } else {
            $this->setError(GetMessage("TN_ERROR_CLIENT_SECRET_NOT_SET"));
        }

        // CHECK FLAG "new_user_email_uniq_check"
        $email_uniq_check = COption::GetOptionString("main", "new_user_email_uniq_check");
        if ($email_uniq_check == "N") {
            $this->setError(GetMessage("TN_ERROR_EMAIL_UNIQ_CHECK_NOT_SET"));
        }

        // CHECK SEND MAIL ENABLED
        if ($this->USE_SEND_MAIL_SETTINGS) {
            $send_mail_enabled = $this->getOption("SEND_MAIL_ENABLED");
            $this->SHOULD_SEND_MAIL = $send_mail_enabled;
        } else {
            $this->SHOULD_SEND_MAIL = TN_DEFAULT_SHOULD_SEND_MAIL;
        }

        // CHECK TEMPLATE ID
        $user_info_template_id = $this->getOption("USER_INFO_TEMPLATE_ID");
        if ($user_info_template_id) {
            $this->USER_INFO_TEMPLATE_ID = $user_info_template_id;
        } else {
            $this->log(getMessage("TN_ERROR_USER_INFO_TEMPLATE_ID"), LOG_LEVEL_INFO);
        }
    }

    private function returnResultWithStatus($status, $data)
    {
        return Array("status" => $status, "data" => $data);
    }

    private function setError($message)
    {
        $this->log('setError');
        $this->ERROR_OCCURRED = true;
        $this->ERROR_MESSAGE = $message;
        $this->log($message, LOG_LEVEL_ERROR);
    }

    private function getOption($name)
    {
        return COption::GetOptionString($this->MODULE_ID, $name);
    }

    private function shouldRegister()
    {
        $this->log('shouldRegister', LOG_LEVEL_INFO);
        $IS_REGISTER_ENABLED = $this->getOption("REGISTER_ENABLED");
        return (true && !$this->ERROR_OCCURRED && $IS_REGISTER_ENABLED);
    }

    private function shouldSendMail()
    {
        $this->log('shouldSendMail', LOG_LEVEL_INFO);
        $SHOULD_SEND_MAIL = $this->SHOULD_SEND_MAIL;
        return $SHOULD_SEND_MAIL;
    }

    private function buildRegisterQuery($data)
    {
        $this->log('buildRegisterQuery', LOG_LEVEL_INFO);
        if ($this->ERROR_OCCURRED) {
            return $this->returnResultWithStatus(false, $data);
        }
        try {
            $FIELD_EMAIL_NAME = "EMAIL";
            $FIELD_NAME_NAME = "NAME";
            $FIELD_LAST_NAME_NAME = "LAST_NAME";

            // Protection against empty fields
            if ($data[$FIELD_LAST_NAME_NAME] == "") {
                $data[$FIELD_LAST_NAME_NAME] = $data[$FIELD_EMAIL_NAME];
            }
            if ($data[$FIELD_NAME_NAME] == "") {
                $data[$FIELD_NAME_NAME] = $data[$FIELD_LAST_NAME_NAME];
            }

            $QUERY_FIELDS = array(
                "login" => $data[$FIELD_EMAIL_NAME],
                "fName" => $data[$FIELD_NAME_NAME],
                "lName" => $data[$FIELD_LAST_NAME_NAME]
            );

            return $this->returnResultWithStatus(true, http_build_query($QUERY_FIELDS));
        } catch (ErrorException $errorException) {
            $this->setError($errorException->getMessage());
            return $this->returnResultWithStatus(false, $data);
        }
    }

    // TODO: maybe make this func private again
    // and use TDataBaseUser->getUserByUserId instead?
    public function getUserRowByUserId($userId)
    {
        $t_auth = null;
        if (isset($this)) {
            $t_auth = $this;
        } else {
            $t_auth = new TrustedAuth();
        }
        $t_auth->log('getUserRowByUserId', LOG_LEVEL_INFO);
        if ($t_auth->ERROR_OCCURRED) {
            return $t_auth->returnResultWithStatus(false, $userId);
        }
        try {
            global $DB;
            $res = null;
            $sql = "SELECT * FROM trn_user WHERE USER_ID = " . $userId;
            $rows = $DB->Query($sql);
            $row = $rows->Fetch();
            return $t_auth->returnResultWithStatus(true, $row);
        } catch (ErrorException $errorException) {
            $t_auth->setError($errorException->getMessage());
            $t_auth->returnResultWithStatus(false, $userId);
        }
    }

    private function bindUsers($bxUser, $tnUser)
    {
        $this->log('bindUsers', LOG_LEVEL_INFO);
        try {
            if ($this->ERROR_OCCURRED) {
                return $this->returnResultWithStatus(false, $bxUser);
            }
            global $DB;

            $bxUserId = $bxUser['ID'];
            $userRow = $this->getUserRowByUserId($bxUserId);

            $timeStamp = date('Y-m-d G:i:s');
            $tnUserId = $tnUser['userID'];
            if (!$userRow["data"]) {
                $sql = "INSERT INTO trn_user
                            (`ID`, `USER_ID`, `TIMESTAMP_X`)
                        VALUES
                            ('" . $tnUserId . "', '" . $bxUserId . "', '" . $timeStamp . "')";
                $DB->Query($sql);
            } else {
                $sql = "UPDATE trn_user
                            SET ID = '" . $tnUserId . "', TIMESTAMP_X = '" . $timeStamp . "'
                        WHERE
                            USER_ID = '" . $bxUserId . "'";
                $DB->Query($sql);
            }
            $token = OAuth2::getFromSession();
            $token = $token->getAccessToken();
            $tnUserInfo = TAuthCommand::pullTnInfo($token, "id", $tnUserId);
            if ($tnUserInfo) {
                $serviceUser = ServiceUser::fromArray($tnUserInfo);
                $user = new TUser();
                $user->setServiceUser($serviceUser);
                $user->setUserId($bxUserId);
                $user->save();
            }
        } catch (ErrorException $errorException) {
            $this->setError($errorException->getMessage());
            return $this->returnResultWithStatus(false, $bxUser);
        }
    }

    private function sendRegisterRequest($data)
    {
        $this->log('sendRegisterRequest', LOG_LEVEL_INFO);
        try {
            $curl = curl_init();
            $buildRegisterQueryResult = $this->buildRegisterQuery($data);

            if (!$buildRegisterQueryResult['status']) {
                return $data;
            } else {
                $curlPostFields = $buildRegisterQueryResult['data'];
            }

            curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($curl, CURLOPT_USERPWD, $this->CLIENT_ID . ":" . $this->CLIENT_SECRET);

            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_URL, $this->SERVICE_REGISTER_URL);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

            curl_setopt($curl, CURLOPT_SSLVERSION, TRUSTED_SSL_VERSION);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

            curl_setopt($curl, CURLOPT_POSTFIELDS, $curlPostFields);

            $result = curl_exec($curl);
            $error = curl_error($curl);
            $info = curl_getinfo($curl);

            if ($error) {
                $this->setError($error);
            } else if ($info['http_code'] !== 200) {
                $this->log($info, LOG_LEVEL_WARNING);
            } else {
                $res = json_decode($result, true);
                $this->log($res, LOG_LEVEL_INFO);
                if ($res['success'] || (!$res['success'] && ($res['code'] == $this->SERVICE_CODE_USER_EXISTS))) {
                    $tnUser = $res['data'];
                    $this->bindUsers($data, $tnUser);
                } else {
                    $this->setError($res['message']);
                }
            }
        } catch (ErrorException $errorException) {
            $this->setError($errorException->getMessage());
        }

        return $data;
    }

    public function registerUser($arFields, $force = false)
    {
        $this->log('registerUser', LOG_LEVEL_INFO);
        if (!$arFields['RESULT']) {
            $this->setError($arFields['RESULT_MESSAGE']);
        }
        $this->log($this->shouldRegister());
        if (!$this->ERROR_OCCURRED) {
            if ($this->shouldRegister() || $force) {
                $this->sendRegisterRequest($arFields);
            }
        }
        return $arFields;
    }

    private function saveOrderIdAndLogout($orderId)
    {
        $this->log('saveOrderIdAndLogout', LOG_LEVEL_INFO);
        if ($this->ERROR_OCCURRED) {
            return;
        } else {
            if (!is_array($_SESSION['SALE_ORDER_ID']))
                $_SESSION['SALE_ORDER_ID'] = array();

            $_SESSION['SALE_ORDER_ID'][] = $orderId;
            try {
                $CUser = new CUser;
                $CUser->Logout();
            } catch (Exception $exception) {
                $this->log($exception->getMessage());
            }
        }
    }

    public function onAfterUserAddHandler($arFields)
    {
        $t_auth = null;
        if (isset($this)) {
            $t_auth = $this;
        } else {
            $t_auth = new TrustedAuth();
        }
        $t_auth->log('onAfterUserAddHandler', LOG_LEVEL_INFO);
        $t_auth->registerUser($arFields);
        return $arFields;
    }

    public function OnAfterUserRegisterHandler($arFields)
    {
        $t_auth = null;
        if (isset($this)) {
            $t_auth = $this;
        } else {
            $t_auth = new TrustedAuth();
        }
        $t_auth->log('OnAfterUserRegisterHandler', LOG_LEVEL_INFO);
        $t_auth->registerUser($arFields);
        return $arFields;
    }

    public function OnAfterUserSimpleRegisterHandler($arFields)
    {
        $t_auth = null;
        if (isset($this)) {
            $t_auth = $this;
        } else {
            $t_auth = new TrustedAuth();
        }
        $t_auth->log('OnAfterUserSimpleRegisterHandler', LOG_LEVEL_INFO);
        $t_auth->registerUser($arFields);
        return $arFields;
    }

    public function OnBeforeUserAddHandler($arFields)
    {
        return $arFields;
    }

    public function OnBeforeUserUpdateHandler($arParams)
    {
        $t_auth = null;
        if (isset($this)) {
            $t_auth = $this;
        } else {
            $t_auth = new TrustedAuth();
        }
        $t_auth->log('OnBeforeUserUpdateHandler', LOG_LEVEL_INFO);
        $userId = $arParams["ID"];
        $newEmail = $arParams["EMAIL"];
        $rsUser = CUser::GetByID($userId);
        $arUser = $rsUser->Fetch();
        if ($arUser) {
            $oldEmail = $arUser["EMAIL"];
        }
        // Check if email was changed
        if ($newEmail != $oldEmail) {
            if (filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
                // Check if other user has the same email
                $filter = array("EMAIL" => $newEmail);
                $arUsersByEmail = CUser::GetList(($by = "id"), ($order = "asc"), $filter);
                while($userByEmail = $arUsersByEmail->GetNext()) {
                    if ($userByEmail["EMAIL"] == $newEmail) {
                        // Duplicate email found
                        return $arParams;
                    }
                }
                $t_auth->sendRegisterRequest($arParams);
            }
        }
        return $arParams;
    }

    public function OnUserLoginHandler()
    {
        return true;
    }

    public function OnBeforeEventSendHandler($arFields, $arTemplate)
    {
        $t_auth = null;
        if (isset($this)) {
            $t_auth = $this;
        } else {
            $t_auth = new TrustedAuth();
        }
        $t_auth->log('OnBeforeEventSendHandler', LOG_LEVEL_INFO);
        $shouldSendMail = $t_auth->shouldSendMail();
        if (($arTemplate['ID'] == $t_auth->USER_INFO_TEMPLATE_ID) && !$shouldSendMail) {
            return false;
        } else {
            return $arFields;
        }
    }

    public function OnSaleComponentOrderOneStepCompleteHandler($orderId)
    {
        $t_auth = null;
        if (isset($this)) {
            $t_auth = $this;
        } else {
            $t_auth = new TrustedAuth();
        }
        $t_auth->log('OnSaleComponentOrderOneStepCompleteHandler', LOG_LEVEL_INFO);
        $t_auth->saveOrderIdAndLogout($orderId);
    }

}

