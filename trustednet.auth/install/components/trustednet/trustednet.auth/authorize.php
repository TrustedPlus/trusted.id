<?php
//BITRIX
require_once $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/bx_root.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php";

require_once $_SERVER["DOCUMENT_ROOT"] . '/bitrix/modules/trustednet.auth/classes/config.php';
require_once TRUSTED_MODULE_PATH . '/classes/general/oauth2.php';

//Debuging
if (TRUSTED_DEBUG) {
    header("Content-Type: text/plain; charset=utf-8");
    echo 'DEBUG' . PHP_EOL;
    echo '========================================' . PHP_EOL;
    debug("GET", $_GET);
    debug("POST", $_POST);
}

function getParam($name, $default = null) {
    return __param($_GET, $name, $default);
}

function postParam($name, $default = null) {
    return __param($_POST, $name, $default);
}

function __param($array, $name, $default) {
    $res = $default;
    if (isset($array[$name])) {
        $res = $array[$name];
    }
    return $res;
}
try {
    if ($code = getParam("code")) {
        if (getParam("final", false)) {
            $res = TAuthCommand::getAccessTokenByCode($code);
            debug("OAuth token from service:", $res);
            $token = OAuth2::fromArray($res);
            debug($token);
            $user_array = TAuthCommand::getUserProfileByToken($token->getAccessToken());
            debug($user_array);
            if (TRUSTED_DB) {
                $user = TDataBaseUser::getUserById($user_array['id']);
                debug("TDataBaseUser::getUserById:", $user);
                if ($user) {
                    //User already registered
                    debug('Old user');
                    if (onRegUserFound) {
                        debug('Event onRegUserFound');
                        onRegUserFound($user);
                    }
                } else {
                    //User not found
                    debug('New user');
                    $user_service = ServiceUser::fromArray($user_array);
                    $user = new TUser();
                    $user->setServiceUser($user_service);

                    if (onBeforeUserInsert) {
                        debug('Event onBeforeUserInsert');
                        onBeforeUserInsert($user);
                    }
                    $user->save();
                }
            }
            $token->setUser($user);
            debug('Token', $token);
            if (onUserAuthorized) {
                debug('Event onUserAuthorized');
                onUserAuthorized($user);
            }
        } else {
            include_once __DIR__ . "/widget.tpl";
        }
    } else {
        $token = OAuth2::getFromSession();
        if (!$token) {
            throw new OAuth2Exception(TRUSTEDNET_ERROR_MSG_TOKEN_NOT_FOUND, TRUSTEDNET_ERROR_CODE_TOKEN_NOT_FOUND, null);
        }
        $token->getAccessToken();
        $token->getUser();
        debug("Token", $token);
    }
} catch (OAuth2Exception $e) {
    if (onOAuth2Exception) {
        onOAuth2Exception($e);
    }
    debug("OAuth2Exception: " . $e->getMessage());
    debug($e->getTrace());
} catch (Exception $e) {
    var_dump($e);
    die();
    if (Exception) {
        onException($e);
    }
    debug("Exception: " . $e->getMessage());
    debug($e->getTrace());
}
debug("END");

