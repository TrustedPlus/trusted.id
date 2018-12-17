<?php

use Trusted\Id;
use Bitrix\Main\Config\Option;

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/bx_root.php");
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

CModule::IncludeModule('trusted.id');


function json_response($resp) {
    // clear the old headers
    header_remove();
    // set the header to make sure cache is forced
    header("Cache-Control: no-transform,public,max-age=300,s-maxage=900");
    // treat this as json
    header('Content-Type: application/json');

    // return the encoded json
    return json_encode(array(
        'success' => $resp["success"],
        'message' => $resp["message"],
        'data' => $resp["data"]
    ));
}

$command = $_GET['command'];
if (isset($command)) {
    $params = $_POST;
    switch ($command) {
        case "find":
            $res = id\AjaxCommand::findUser($_GET);
            break;
        case "remove":
            $res = id\AjaxCommand::removeUserBind($_GET);
            break;
        default:
            $res = array("success" => false, "message" => "Unknown command '" . $command . "'");
    }
}

echo json_response($res);

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_after.php");



