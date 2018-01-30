<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/urlrewrite.php';
require $_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php";

$APPLICATION->SetPageProperty("show_catalog_nemu", "N");
$APPLICATION->SetTitle("<span class=\"b-big-title\">Внимание</span> - произошла ошибка!");

IncludeModuleLangFile(__FILE__);

$code = $_GET['c'];
switch ($code) {
    case "1":
    case "2":
    case "3":
    case "4":
    case "5":
    case "6":
        ?>
        <b class="message">
            <? echo GetMessage("TN_AUTH_ERROR"); ?>
            </br>
            <? echo GetMessage("TN_AUTH_ERROR_" . $code); ?>
        </b>
        <?
        break;
    default:
        ?>
        <b class="message">
            <? echo GetMessage("TN_AUTH_ERROR"); ?>
            </br>
            <?
            if (strpos($_GET['e'], "Bad credentials") !== false) {
                echo GetMessage("TN_AUTH_BAD_CREDENTIALS");
            } else {
                echo $_GET['e'];
            }
            ?>
        </b>
    <?
}

require $_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php";

