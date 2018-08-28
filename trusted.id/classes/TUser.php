<?php

namespace Trusted\Id;

require_once __DIR__ . '/../config.php';
require_once TR_ID_MODULE_PATH . '/classes/Utils.php';

session_start();

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
                //Utils::debug($arUser);
                if ($arUser['id'] == $this->id) {
                    $res = ServiceUser::fromArray($arUser);
                } else {
                    throw new OAuth2Exception(TR_ID_ERROR_MSG_DIFFERENT_USER_ID, TRUSTEDNET_ERROR_CODE_DIFFERENT_USER_ID, null);
                }
            } else {
                throw new OAuth2Exception(TR_ID_ERROR_MSG_TOKEN_NOT_FOUND, TRUSTEDNET_ERROR_CODE_TOKEN_NOT_FOUND, null);
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

