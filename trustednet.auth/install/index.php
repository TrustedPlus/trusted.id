<?php

Class trustednet_auth extends CModule
{

    var $MODULE_ID = "trustednet.auth";
    var $MODULE_NAME;
    var $MODULE_DESCRIPTION;
    var $MODULE_VERSION;
    var $MODULE_VERSION_DATE;
    var $PARTNER_NAME;
    var $PARTNER_URI;

    function trustednet_auth()
    {
        self::__construct();
    }

    function __construct()
    {
        $arModuleVersion = array();

        include substr(__FILE__, 0, -10) . "/version.php";

        $this->MODULE_ID = "trustednet.auth";
        $this->MODULE_NAME = GetMessage("TN_AUTH_MODULE_NAME");
        $this->MODULE_DESCRIPTION = GetMessage("TN_AUTH_MODULE_DESCRIPTION");
        $this->MODULE_VERSION = "1.2.6";
        $this->MODULE_VERSION_DATE = "2018-02-01 9:00:00";
        $this->PARTNER_NAME = GetMessage("TN_AUTH_PARTNER_NAME");
        $this->PARTNER_URI = "http://www.digt.ru";
    }

    function RegisterEventHandlers()
    {
        RegisterModuleDependences('main', 'OnAfterUserAdd', $this->MODULE_ID, 'TrustedAuth', 'OnAfterUserAddHandler');
        RegisterModuleDependences('main', 'OnAfterUserRegister', $this->MODULE_ID, 'TrustedAuth', 'OnAfterUserRegisterHandler');
        RegisterModuleDependences('main', 'OnAfterUserSimpleRegister', $this->MODULE_ID, 'TrustedAuth', 'OnAfterUserSimpleRegisterHandler');
        RegisterModuleDependences('main', 'OnBeforeUserUpdate', $this->MODULE_ID, 'TrustedAuth', 'OnBeforeUserUpdateHandler');
        RegisterModuleDependences('main', 'OnBeforeUserAdd', $this->MODULE_ID, 'TrustedAuth', 'OnBeforeUserAddHandler');
        RegisterModuleDependences('main', 'OnBeforeEventSend', $this->MODULE_ID, 'TrustedAuth', 'OnBeforeEventSendHandler');
        RegisterModuleDependences('main', 'OnUserLogin', $this->MODULE_ID, 'TrustedAuth', 'OnUserLoginHandler');

        RegisterModuleDependences('sale', 'OnSaleComponentOrderOneStepComplete', $this->MODULE_ID, 'TrustedAuth', 'OnSaleComponentOrderOneStepCompleteHandler');
    }

    function UnRegisterEventHandlers()
    {
        UnRegisterModuleDependences('main', 'OnAfterUserAdd', $this->MODULE_ID, 'TrustedAuth');
        UnRegisterModuleDependences('main', 'OnAfterUserRegister', $this->MODULE_ID, 'TrustedAuth');
        UnRegisterModuleDependences('main', 'OnAfterUserSimpleRegister', $this->MODULE_ID, 'TrustedAuth');
        UnRegisterModuleDependences('main', 'OnBeforeUserUpdate', $this->MODULE_ID, 'TrustedAuth');
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
        //$this->UnInstallDB();
        $this->UnRegisterEventHandlers();
        UnRegisterModule($this->MODULE_ID);
        $APPLICATION->IncludeAdminFile(GetMessage("MOD_INSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/" . $this->MODULE_ID . "/install/unstep.php");
    }

    function InstallDB()
    {
        // To update table after it was created
        // ALTER TABLE trn_user ADD TN_FAM_NAME varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL
        global $DB;
        $sql = "CREATE TABLE IF NOT EXISTS `trn_user` (
                    `ID` int(11) NOT NULL,
                    `USER_ID` int(18) DEFAULT NULL,
                    `TN_FAM_NAME` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
                    `TN_GIV_NAME` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
                    `TN_EMAIL` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
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
        CopyDirFiles(
            $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/" . $this->MODULE_ID . "/install/components/",
            $_SERVER["DOCUMENT_ROOT"] . "/bitrix/components/",
            true, true
        );
        CopyDirFiles(
            $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/" . $this->MODULE_ID . "/install/admin",
            $_SERVER["DOCUMENT_ROOT"] . "/bitrix/admin",
            true, false
        );
        CopyDirFiles(
            $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/" . $this->MODULE_ID . "/install/themes",
            $_SERVER["DOCUMENT_ROOT"] . "/bitrix/themes",
            true, true
        );
        return true;
    }

    function UnInstallFiles()
    {
        DeleteDirFilesEx("/bitrix/components/trustednet/" . $this->MODULE_ID);
        DeleteDirFiles(
            $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/" . $this->MODULE_ID . "/install/admin/",
            $_SERVER["DOCUMENT_ROOT"] . "/bitrix/admin"
        );
        DeleteDirFiles(
            $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/" . $this->MODULE_ID . "/install/themes/.default/",
            $_SERVER["DOCUMENT_ROOT"] . "/bitrix/themes/.default/"
        );
        DeleteDirFilesEx("/bitrix/themes/.default/icons/" . $this->MODULE_ID);
        return true;
    }

    function LogOutTrustedNetUser()
    {
        if (isset($_SESSION['TRUSTEDNET']['OAUTH'])) {
            unset($_SESSION['TRUSTEDNET']['OAUTH']);
        }
    }
}

