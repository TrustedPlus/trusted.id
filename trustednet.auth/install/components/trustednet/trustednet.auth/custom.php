<?php

/**
 *
 * @global type $USER
 * @param \TUser $user
 */
function onRegUserFound($user) {
    global $USER;
    if ($USER->IsAuthorized()) {
        $user->setUserId($USER->GetID());
        $user->save();
    }
}

/**
 *
 * @param \OAuth2Exception $e
 */
function onOAuth2Exception($e) {
    global $USER;
    ShowMessage($e->getMessage());
    OAuth2::remove();
    if (!TRUSTED_DEBUG) {
        if ($USER) {
            $USER->Logout();
            header("Location: ".TRUSTED_URI_MODULE_AUTH.'/error.php?c='.$e->getCode().'&e='.  urlencode($e->getMessage()));
        }
    }
}

/**
 *
 * @param \Exception $e
 */
function onException($e) {
    ShowMessage($e->getMessage());
}

/**
 *
 * @global type $USER
 * @param \TUser $user
 * @throws OAuth2Exception
 */
function onBeforeUserInsert(&$user) {
    global $USER;
    $email = $user->getServiceUser()->getEmail();
    if (!$email) {
        throw new OAuth2Exception(TRUSTEDNET_ERROR_MSG_ACCOUNT_NO_EMAIL, TRUSTEDNET_ERROR_CODE_ACCOUNT_NO_EMAIL, null);
    }
    $bxUser = NULL;
    if ($USER && $USER->IsAuthorized()) {
        $savedUser = TDataBaseUser::getUserByUserId($USER->GetID());
        if ($savedUser) {
            TDataBaseUser::removeUser($savedUser);
        }
        $user->setUserId($USER->GetID());
    } else if ($bxUser = bitrixGetUserByEmail($email)) {
        $user->setUserId($bxUser["ID"]);
    } else {
        $bxUser = new CUser();
        $srvUser = $user->getServiceUser();
        $psw = randomPassword();
        $bx_user_array = array(
            "NAME" => $srvUser->getGivenName(),
            "LAST_NAME" => $srvUser->getFamilyName(),
            "EMAIL" => $srvUser->getEmail(),
            "LOGIN" => $srvUser->getEmail(),
            "ACTIVE" => "Y",
            "GROUP_ID" => array(4),
            "PASSWORD" => $psw,
            "CONFIRM_PASSWORD" => $psw);
        debug("New user data", $bx_user_array);
        $UserID = $bxUser->Add($bx_user_array);
        debug("User id", $UserID);
        if (!$UserID) {
            debug ("Error user create", $bxUser->LAST_ERROR);
            throw new OAuth2Exception(TRUSTEDNET_ERROR_MSG_ACCOUNT_CREATE, TRUSTEDNET_ERROR_CODE_ACCOUNT_CREATE, null);
        }
        $user->setUserId($UserID);
    }
}

function bitrixGetUserByEmail($email) {
    $by = 'id';
    $order = 'desc';
    $loginUsers = CUser::GetList($by, $order, array("EMAIL" => $email, "ACTIVE" => "Y"));
    $res = $loginUsers->Fetch();
    return $res;
}

/**
 * @global type $USER
 * @param \TUser $user
 */
function onUserAuthorized($user) {
    debug("onUserAuthorize");
    global $USER;
    if (!($USER && $USER->IsAuthorized())) {
        $bxUser = new CUser();
        if (!$bxUser->authorize($user->getUserId())) {
            debug("Not authorized");
        } else {
            debug("Authorized");
        }
    }
    $REDIRECT_URL = COption::GetOptionString("trustednet.auth", "REDIRECT_URL", "personal");
    $REDIRECT_URL = "/" . $REDIRECT_URL . "/";
    print_r($REDIRECT_URL);
    if (!TRUSTED_DEBUG) {
        header("Location: " . TRUSTED_URI_HOST . $REDIRECT_URL);
    }
}

