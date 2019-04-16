<?php

namespace Trusted\Id;

/**
 * Controllers for AJAX requests.
 *
 * Used for interaction of bitrix server with opened pages and signing client.
 */
class AjaxCommand {

    static private function s2o($message, $success = false, $data = null) {
        return array("success" => $success, "message" => $message, "data" => $data);
    }


    static function findUser($params) {
        if ($params["searchValue"] == "") {
            return self::s2o("Value cannot be empty!", false);
        }

        $searchType = $params["searchTypeValue"];
        switch ($searchType) {
            case "uid":
                $user = TAuthCommand::findTnUserDataById($params["searchValue"]);
                return self::s2o(null, true, $user);

            case "email":
            case "phone":
            case "photo":
                $user = TAuthCommand::findTnUserData($params["searchTypeValue"], $params["searchValue"]);
                return self::s2o(null, true, $user);
            default:
                return self::s2o("Invalid searchTypeValue!", false);
        }
    }


    static function removeUserBind($params) {
        if ($params["userID"] == "") {
            return self::s2o("Value cannot be empty!", false);
        }
        TDataBaseUser::removeUserByUserId($params["userID"]);
        return self::s2o(null, true);
    }

}

