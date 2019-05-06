<?php
use Trusted\Id;

//BITRIX
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/bx_root.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';

CModule::IncludeModule('trusted.id');

//Debuging
if (TR_ID_DEBUG) {
    header('Content-Type: text/plain; charset=utf-8');
    echo 'DEBUG' . PHP_EOL;
    echo '========================================' . PHP_EOL;
    Id\Utils::debug('GET', $_GET);
    Id\Utils::debug('POST', $_POST);
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
    // Widget checks if user is registered in the bitrix but not on the tn service
    if ($userEmail = postParam('login')) {
        $protocol = isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0';
        $users = CUser::GetList($by = 'id', $order = 'asc', array('EMAIL' => $userEmail));
        while ($user = $users->Fetch()) {
            // User with same email is found
            if ($user['EMAIL'] === $userEmail) {
                // User already has binding to tn service user
                if (Id\TDataBaseUser::getUserByUserId($user['ID'])) {
                    header($protocol . ' 201 User is already registered');
                    die();
                }
                // Register user on the tn service
                $user['RESULT'] = true;
                $Auth = new Id\Auth;
                $Auth->registerUser($user, true);
                header($protocol . ' 200 User found');
                die();
            }
        }
        header($protocol . ' 404 User not found');
        die();
    // OAuth authorization
    } else if ($code = getParam('code')) {
        if (getParam('final', false)) {
            $res = Id\TAuthCommand::getAccessTokenByCode($code);
            Id\Utils::debug('OAuth token from service:', $res);
            $token = Id\OAuth2::fromArray($res);
            Id\Utils::debug($token);
            $user_array = Id\TAuthCommand::getUserProfileByToken($token->getAccessToken());
            Id\Utils::debug($user_array);
            if (TR_ID_DB) {
                $user = Id\TDataBaseUser::getUserById($user_array['id']);
                Id\Utils::debug('TDataBaseUser::getUserById:', $user);
                if ($user) {
                    //User already registered
                    Id\Utils::debug('Old user');
                    Id\Utils::debug('Event onRegUserFound');
                    Id\Custom::onRegUserFound($user);
                } else {
                    //User not found
                    Id\Utils::debug('New user');
                    $user_service = Id\ServiceUser::fromArray($user_array);
                    $user = new Id\TUser();
                    $user->setServiceUser($user_service);

                    Id\Utils::debug('Event onBeforeUserInsert');
                    Id\Custom::onBeforeUserInsert($user);
                    $user->save();
                }
            }
            $token->setUser($user);
            Id\Utils::debug('Token', $token);

            $redirectUrl = getParam('state');
            Id\Utils::debug('State get param (redirectUrl)', $redirectUrl);
            $onceAction = getParam('onceAction');
            Id\Utils::debug('State get param (onceAction)', $onceAction);
            $redirectUrl .= $onceAction ? true : "";
            Id\Utils::debug('Event onUserAuthorized');
            Id\Custom::onUserAuthorized($user, $redirectUrl, $onceAction);
        } else {
            include_once __DIR__ . '/widget.tpl';
        }
    } else {
        $token = Id\OAuth2::getFromSession();
        if (!$token) {
            throw new Id\OAuth2Exception(TR_ID_ERROR_MSG_TOKEN_NOT_FOUND, TR_ID_ERROR_CODE_TOKEN_NOT_FOUND, null);
        }
        $token->getAccessToken();
        $token->getUser();
        Id\Utils::debug('Token', $token);
    }
} catch (Id\OAuth2Exception $e) {
    Id\Custom::onOAuth2Exception($e);
    Id\Utils::debug('OAuth2Exception: ' . $e->getMessage());
    Id\Utils::debug($e->getTrace());
} catch (Exception $e) {
    var_dump($e);
    die();
    Id\Custom::onException($e);
    Id\Utils::debug('Exception: ' . $e->getMessage());
    Id\Utils::debug($e->getTrace());
}
Id\Utils::debug('END');

