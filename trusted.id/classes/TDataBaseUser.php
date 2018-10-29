<?php

namespace Trusted\Id;

require_once __DIR__ . '/../config.php';
require_once TR_ID_MODULE_PATH . '/classes/Utils.php';

session_start();

class TDataBaseUser {

    static function getBitrixAndTnUsers ($by, $order, $filter) {
        $find_id = (string)$filter['ID'];
        $find_login = (string)$filter['LOGIN'];
        $find_name = (string)$filter['NAME'];
        $find_email = (string)$filter['EMAIL'];
        $find_tn_id = (string)$filter['TN_ID'];
        $find_tn_giv_name = (string)$filter['TN_GIV_NAME'];
        $find_tn_fam_name = (string)$filter['TN_FAM_NAME'];
        $find_tn_email = (string)$filter['TN_EMAIL'];

        $sqlWhere = array();
        if ($find_id !== '') {
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
            $sqlWhere[] = "TU.TR_ID = '" . $find_tn_id . "'";
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
                TU.TR_ID as TN_ID, TU.GIVEN_NAME as TN_GIV_NAME, TU.FAMILY_NAME as TN_FAM_NAME, TU.EMAIL as TN_EMAIL
            FROM
                b_user as BU
            LEFT JOIN
                " . TR_ID_DB_TABLE_USER . " as TU
            ON
                BU.ID=TU.BX_ID";

        // Filtering
        if (count($sqlWhere)) {
            $sql .= " WHERE " . implode(" AND ", $sqlWhere);
        }

        // Ordering
        $fields = array(
            'ID' => 'BU.ID',
            'LOGIN' => 'BU.LOGIN',
            'NAME' => "CONCAT(BU.NAME, ' ', BU.LAST_NAME)",
            'EMAIL' => 'BU.EMAIL',
            'TN_ID' => 'TU.TR_ID',
            'TN_GIV_NAME' => 'TU.GIVEN_NAME',
            'TN_FAM_NAME' => 'TU.FAMILY_NAME',
            'TN_EMAIL' => 'TU.EMAIL',
        );
        $by = strtoupper($by);
        $order = strtoupper($order);
        if (array_key_exists($by, $fields)) {
            if ($order != 'DESC') {
                $order = 'ASC';
            }
            $sql .= ' ORDER BY ' . $fields[$by] . ' ' . $order . ';';
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
        $sql = "SELECT * FROM " . TR_ID_DB_TABLE_USER . " WHERE TR_ID = '" . $id . "'";
        $res = null;
        $rows = $DB->Query($sql);
        if ($row = $rows->Fetch()) {
            $array = array(
                'id' => $row['TR_ID'],
                'userId' => $row['BX_ID']
            );
            $res = TUser::fromArray($array);
        }
        return $res;
    }

    static function getUserByUserId($userId) {
        global $DB;
        $sql = "SELECT * FROM " . TR_ID_DB_TABLE_USER . " WHERE BX_ID = " . $userId;
        $res = null;
        $rows = $DB->Query($sql);
        if ($row = $rows->Fetch()) {
            $array = array(
                'id' => $row['TR_ID'],
                'userId' => $row['BX_ID'],
                'givenName' => $row['GIVEN_NAME'],
                'familyName' => $row['FAMILY_NAME'],
                'email' => $row['EMAIL'],
            );
            $res = TUser::fromArray($array);
        }
        return $res;
    }

    static function getUserByEmail($email) {
        global $DB;
        $sql = "SELECT * FROM " . TR_ID_DB_TABLE_USER . " WHERE BX_ID <> '' AND EMAIL = '" . $email . "'";
        $res = null;
        $rows = $DB->Query($sql);
        if ($row = $rows->Fetch()) {
            $array = array(
                'id' => $row['TR_ID'],
                'userId' => $row['BX_ID']
            );
            $res = TUser::fromArray($array);
        }
        return $res;
    }

    static function saveUser($user) {
        if (TDataBaseUser::getUser($user)) {
            global $DB;
            //Save
            Utils::debug('Save User to DataBase');
            $userId = 'NULL';
            if ($user->getUserId()) {
                $userId = "'" . $user->getUserId() . "'";
            }
            $familyName = 'NULL';
            if ($user->getFamilyName()) {
                $familyName = "'" . $user->getFamilyName() . "'";
            }
            $givenName = 'NULL';
            if ($user->getGivenName()) {
                $givenName = "'" . $user->getGivenName() . "'";
            }
            $email = 'NULL';
            if ($user->getEmail()) {
                $email = "'" . $user->getEmail() . "'";
            }
            $username = 'NULL';
            if ($user->getUsername()) {
                $username = "'" . $user->getUsername() . "'";
            }
            $sql = "UPDATE " . TR_ID_DB_TABLE_USER . " SET "
                    . "BX_ID = " . $userId . ", "
                    . "FAMILY_NAME = " . $familyName . ", "
                    . "GIVEN_NAME = " . $givenName . ", "
                    . "EMAIL = " . $email . ", "
                    . "USERNAME = " . $username . " "
                    . "WHERE TR_ID = " . $user->getId();
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
        Utils::debug('Insert User to DataBase');
        $userId = 'NULL';
        if ($user->getUserId()) {
            $userId = "'" . $user->getUserId() . "'";
        }
        $familyName = 'NULL';
        if ($user->getFamilyName()) {
            $familyName = "'" . $user->getFamilyName() . "'";
        }
        $givenName = 'NULL';
        if ($user->getGivenName()) {
            $givenName = "'" . $user->getGivenName() . "'";
        }
        $email = 'NULL';
        if ($user->getEmail()) {
            $email = "'" . $user->getEmail() . "'";
        }
        $username = 'NULL';
        if ($user->getUsername()) {
            $username = "'" . $user->getUsername() . "'";
        }
        $sql = "INSERT INTO " .
                    TR_ID_DB_TABLE_USER . " (TR_ID, BX_ID, FAMILY_NAME, GIVEN_NAME, EMAIL, USERNAME)
                VALUES ("
                    . $user->getId() . ", "
                    . $userId . ", "
                    . $familyName . ", "
                    . $givenName . ", "
                    . $email . ", "
                    . $username
                    . ")";
        $DB->Query($sql);
    }

    static function removeUserById($id) {
        global $DB;
        Utils::debug('removeUserById');
        $sql = "DELETE FROM " . TR_ID_DB_TABLE_USER . " WHERE "
                . "TR_ID = " . $id;
        Utils::debug('SQL: ', $sql);
        $DB->Query($sql);
    }

    static function removeUserByUserId($userId) {
        global $DB;
        Utils::debug('removeUserByUserId');
        $sql = "DELETE FROM " . TR_ID_DB_TABLE_USER . " WHERE "
                . "BX_ID = " . $userId;
        Utils::debug('SQL: ', $sql);
        $DB->Query($sql);
    }

    static function removeUser($user) {
        Utils::debug('removeUser');
        TDataBaseUser::removeUserById($user->getId());
    }

}

