<?php

Class trustednet_auth extends CModule
{

    var $MODULE_ID = "trustednet.auth";
    var $MODULE_NAME = "TrustedNet Auth";
    var $MODULE_DESCRIPTION = "Модуль аутентификации и авторизации пользователей TrustedNet Auth";
    var $MODULE_VERSION = "1.2.4";
    var $MODULE_VERSION_DATE = "2017-07-25 9:00:00";
    var $PARTNER_NAME = 'ООО "Цифровые технологии"';
    var $PARTNER_URI = "http://www.digt.ru";

    function trustednet_auth()
    {
        self::__construct();
    }

    function __construct()
    {
        $arModuleVersion = array();

        include(substr(__FILE__, 0, -10) . "/version.php");

        $this->MODULE_VERSION = $arModuleVersion["VERSION"];
        $this->MODULE_VERSION_DATE = $this->MODULE_VERSION_DATE;
        $this->MODULE_NAME = $this->MODULE_NAME;
        $this->MODULE_DESCRIPTION = $this->MODULE_DESCRIPTION;
        $this->PARTNER_NAME = 'ООО "Цифровые технологии"';
        $this->PARTNER_URI = "http://www.digt.ru";
    }

    function RegisterEventHandlers()
    {
        RegisterModuleDependences('main', 'OnAfterUserAdd', $this->MODULE_ID, 'TrustedAuth', 'OnAfterUserAddHandler');
        RegisterModuleDependences('main', 'OnBeforeUserAdd', $this->MODULE_ID, 'TrustedAuth', 'OnBeforeUserAddHandler');
        RegisterModuleDependences('main', 'OnBeforeEventSend', $this->MODULE_ID, 'TrustedAuth', 'OnBeforeEventSendHandler');
        RegisterModuleDependences('main', 'OnUserLogin', $this->MODULE_ID, 'TrustedAuth', 'OnUserLoginHandler');

        RegisterModuleDependences('sale', 'OnSaleComponentOrderOneStepComplete', $this->MODULE_ID, 'TrustedAuth', 'OnSaleComponentOrderOneStepCompleteHandler');
    }

    function UnRegisterEventHandlers()
    {
        UnRegisterModuleDependences('main', 'OnAfterUserAdd', $this->MODULE_ID, 'TrustedAuth');
        UnRegisterModuleDependences('main', 'OnBeforeUserAdd', $this->MODULE_ID, 'TrustedAuth');
        UnRegisterModuleDependences('main', 'OnBeforeEventSend', $this->MODULE_ID, 'TrustedAuth');
        UnRegisterModuleDependences('main', 'OnUserLogin', $this->MODULE_ID, 'TrustedAuth');

        UnRegisterModuleDependences('sale', 'OnSaleComponentOrderOneStepComplete', $this->MODULE_ID, 'TrustedAuth');
    }

    function DoInstall()
    {
        global $DOCUMENT_ROOT, $APPLICATION;
        $this->InstallFiles();
        $this->InstallDB();
        $this->RegisterEventHandlers();
        RegisterModule($this->MODULE_ID);
        $APPLICATION->IncludeAdminFile(GetMessage("MOD_INSTALL_TITLE"), $DOCUMENT_ROOT . "/bitrix/modules/" . $this->MODULE_ID . "/install/step.php");
    }

    function DoUninstall()
    {
        global $DB, $APPLICATION, $step;
        $this->LogOutTrustedNetUser();
        $this->UnInstallFiles();
        $this->UnInstallDB();
        $this->UnRegisterEventHandlers();
        UnRegisterModule($this->MODULE_ID);
        $APPLICATION->IncludeAdminFile(GetMessage("MOD_INSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/" . $this->MODULE_ID . "/install/unstep.php");
    }

    function InstallDB()
    {
        global $DB;
        $sql = "CREATE TABLE IF NOT EXISTS `trn_user` (
                    `ID` int(11) NOT NULL,
                    `USER_ID` int(18) DEFAULT NULL,
                    `TIMESTAMP_X` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (`ID`)
                    ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
        $DB->Query($sql);
    }

    function UnInstallDB()
    {
        global $DB;
        $sql = "DROP TABLE IF EXISTS `trn_user`";
        $DB->Query($sql);
    }

    function InstallFiles()
    {
        CopyDirFiles($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/" . $this->MODULE_ID . "/install/components/", $_SERVER["DOCUMENT_ROOT"] . "/bitrix/components/", true, true);
//        CopyDirFiles($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/" . $this->MODULE_ID . "/install/admin", $_SERVER["DOCUMENT_ROOT"] . "/bitrix/admin", true, false);
        return true;
    }

    function UnInstallFiles()
    {
        DeleteDirFilesEx("/bitrix/components/trustednet/" . $this->MODULE_ID);
//        DeleteDirFiles($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/" . $this->MODULE_ID . "/install/admin/", $_SERVER["DOCUMENT_ROOT"] . "/bitrix/admin");
        return true;
    }

    function LogOutTrustedNetUser()
    {
        require_once (__DIR__ . '/components/trustednet/trustednet.auth/oauth2.php');
        OAuth2::remove();
    }
}

