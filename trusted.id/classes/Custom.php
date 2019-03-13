<?php

namespace Trusted\Id;
use Bitrix\Main\Config\Option;

class Custom
{

    /**
     *
     * @global type $USER
     * @param \TUser $user
     */
    function onRegUserFound($user) {
        global $USER;
        if ($USER->IsAuthorized()) {
            $USER->Logout();
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
        if (!TR_ID_DEBUG) {
            if ($USER) {
                //$USER->Logout();
                header('Location: '.TR_ID_URI_MODULE_AUTH.'/error.php?c='.$e->getCode().'&e='.  urlencode($e->getMessage()));
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

    function bitrixGetUserByEmail($email) {
        $by = 'id';
        $order = 'desc';
        $loginUsers = \CUser::GetList($by, $order, array('EMAIL' => $email, 'ACTIVE' => 'Y'));
        $res = $loginUsers->Fetch();
        return $res;
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
            throw new OAuth2Exception(TR_ID_ERROR_MSG_ACCOUNT_NO_EMAIL, TR_ID_ERROR_CODE_ACCOUNT_NO_EMAIL, null);
        }
        $bxUser = NULL;
        if ($USER && $USER->IsAuthorized()) {
            $savedUser = TDataBaseUser::getUserByUserId($USER->GetID());
            if ($savedUser) {
                TDataBaseUser::removeUser($savedUser);
            }
            $user->setUserId($USER->GetID());
        } else if ($bxUser = Custom::bitrixGetUserByEmail($email)) {
            $user->setUserId($bxUser['ID']);
        } else {
            $bxUser = new \CUser();
            $srvUser = $user->getServiceUser();
            $psw = Utils::randomPassword();
            $bx_user_array = array(
                'NAME' => $srvUser->getGivenName(),
                'LAST_NAME' => $srvUser->getFamilyName(),
                'EMAIL' => $srvUser->getEmail(),
                'LOGIN' => $srvUser->getEmail(),
                'ACTIVE' => 'Y',
                'GROUP_ID' => array(4),
                'PASSWORD' => $psw,
                'CONFIRM_PASSWORD' => $psw);
            Utils::debug('New user data', $bx_user_array);
            $UserID = $bxUser->Add($bx_user_array);
            Utils::debug('User id', $UserID);
            if (!$UserID) {
                Utils::debug ('Error user create', $bxUser->LAST_ERROR);
                throw new OAuth2Exception(TR_ID_ERROR_MSG_ACCOUNT_CREATE, TR_ID_ERROR_CODE_ACCOUNT_CREATE, null);
            }
            $user->setUserId($UserID);
        }
    }

    /**
     * @global type $USER
     * @param \TUser $user
     * @param string $redirectUrl
     */
    function onUserAuthorized($user, $redirectUrl) {
        Utils::debug('onUserAuthorize');
        global $USER;
        if (!($USER && $USER->IsAuthorized())) {
            $bxUser = new \CUser();
            if (!$bxUser->authorize($user->getUserId())) {
                Utils::debug('Not authorized');
            } else {
                Utils::debug('Authorized');
            }
        }

        if (!$redirectUrl) {
            $redirectUrl = TR_ID_URI_HOST;
        }
        if (!TR_ID_DEBUG) {
            header('Location: ' . $redirectUrl);
        }
    }

}

