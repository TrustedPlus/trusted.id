<?php

namespace Trusted\Id;

require_once __DIR__ . '/../config.php';
require_once TR_ID_MODULE_PATH . '/classes/Utils.php';

session_start();

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
        return $this->avatarUrl . '?access_token=' . $accessToken;
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
        $props = $array['properties'];
        foreach ($props as $key => &$value) {
            if ($value['type'] == 'thumbnailUrl') {
                $res->avatarUrl = TR_ID_COMMAND_REST . '/storage/' . $value['value'];
                break;
            }
        }
        if (!$res->avatarUrl) {
            $res->avatarUrl = TR_ID_COMMAND_URI_HOST . '/static/new/img/ava.jpg';
        }
        return $res;
    }

}

