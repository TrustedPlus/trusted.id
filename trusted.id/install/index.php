<?php

use Bitrix\Main\Config\Option;
use Trusted\Id;
use Bitrix\Main\ModuleManager;


Class trusted_id extends CModule
{
    const MODULE_ID = 'trusted.id';
    var $MODULE_ID = 'trusted.id';
    var $MODULE_NAME;
    var $MODULE_DESCRIPTION;
    var $MODULE_VERSION;
    var $MODULE_VERSION_DATE;
    var $PARTNER_NAME;
    var $PARTNER_URI;

    function trusted_id()
    {
        self::__construct();
    }

    function __construct()
    {
        $arModuleVersion = array();
        include __DIR__ . '/version.php';
        $this->MODULE_ID = 'trusted.id';
        $this->MODULE_NAME = GetMessage('TR_ID_MODULE_NAME');
        $this->MODULE_DESCRIPTION = GetMessage('TR_ID_MODULE_DESCRIPTION');
        $this->MODULE_VERSION = $arModuleVersion['VERSION'];
        $this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];
        $this->PARTNER_NAME = GetMessage('TR_ID_PARTNER_NAME');
        $this->PARTNER_URI = 'http://www.digt.ru';
    }

    function RegisterEventHandlers()
    {
        RegisterModuleDependences('main', 'OnAfterUserAdd', self::MODULE_ID, '\Trusted\Id\Auth', 'OnAfterUserAddHandler');
        RegisterModuleDependences('main', 'OnAfterUserRegister', self::MODULE_ID, '\Trusted\Id\Auth', 'OnAfterUserRegisterHandler');
        RegisterModuleDependences('main', 'OnAfterUserSimpleRegister', self::MODULE_ID, '\Trusted\Id\Auth', 'OnAfterUserSimpleRegisterHandler');
        RegisterModuleDependences('main', 'OnBeforeUserUpdate', self::MODULE_ID, '\Trusted\Id\Auth', 'OnBeforeUserUpdateHandler');
        RegisterModuleDependences('main', 'OnBeforeUserAdd', self::MODULE_ID, '\Trusted\Id\Auth', 'OnBeforeUserAddHandler');
        RegisterModuleDependences('main', 'OnBeforeEventSend', self::MODULE_ID, '\Trusted\Id\Auth', 'OnBeforeEventSendHandler');
        RegisterModuleDependences('main', 'OnUserLogin', self::MODULE_ID, '\Trusted\Id\Auth', 'OnUserLoginHandler');
        RegisterModuleDependences('main', 'OnUserLogout', self::MODULE_ID, '\Trusted\Id\Auth', 'OnUserLogoutHandler');

        RegisterModuleDependences('sale', 'OnSaleComponentOrderOneStepComplete', self::MODULE_ID, '\Trusted\Id\Auth', 'OnSaleComponentOrderOneStepCompleteHandler');
    }

    function UnRegisterEventHandlers()
    {
        UnRegisterModuleDependences('main', 'OnAfterUserAdd', self::MODULE_ID, '\Trusted\Id\Auth');
        UnRegisterModuleDependences('main', 'OnAfterUserRegister', self::MODULE_ID, '\Trusted\Id\Auth');
        UnRegisterModuleDependences('main', 'OnAfterUserSimpleRegister', self::MODULE_ID, '\Trusted\Id\Auth');
        UnRegisterModuleDependences('main', 'OnBeforeUserUpdate', self::MODULE_ID, '\Trusted\Id\Auth');
        UnRegisterModuleDependences('main', 'OnBeforeUserAdd', self::MODULE_ID, '\Trusted\Id\Auth');
        UnRegisterModuleDependences('main', 'OnBeforeEventSend', self::MODULE_ID, '\Trusted\Id\Auth');
        UnRegisterModuleDependences('main', 'OnUserLogin', self::MODULE_ID, '\Trusted\Id\Auth');
        UnRegisterModuleDependences('main', 'OnUserLogout', self::MODULE_ID, '\Trusted\Id\Auth');

        UnRegisterModuleDependences('sale', 'OnSaleComponentOrderOneStepComplete', self::MODULE_ID, '\Trusted\Id\Auth');
    }

    //needed to pass check on install tr ca docs
    // to do: add check version with tr ca docs core
    function CoreAndModuleAreCompatible() {
        return "ok";
    }

    function DoInstall()
    {
        global $DOCUMENT_ROOT, $APPLICATION;
        self::InstallFiles();
        self::InstallDB();
        self::RegisterEventHandlers();
        ModuleManager::RegisterModule(self::MODULE_ID);
        // $APPLICATION->IncludeAdminFile(GetMessage('MOD_INSTALL_TITLE'), $DOCUMENT_ROOT . '/bitrix/modules/' . self::MODULE_ID . '/install/step.php');
    }

    function DoUninstall()
    {
        global $DB, $APPLICATION, $step;
        self::LogOutTrustedUser();
        self::UnInstallFiles();
        //$this->UnInstallDB();
        self::UnRegisterEventHandlers();
        ModuleManager::UnRegisterModule(self::MODULE_ID);
        // $APPLICATION->IncludeAdminFile(GetMessage('MOD_INSTALL_TITLE'), $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/' . self::MODULE_ID . '/install/unstep.php');
    }

    function InstallDB()
    {
        global $DB;
        $sql = "CREATE TABLE IF NOT EXISTS `tr_id_users` (
                    `TR_ID` int(11) NOT NULL,
                    `BX_ID` int(18) DEFAULT NULL,
                    `GIVEN_NAME` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
                    `FAMILY_NAME` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
                    `EMAIL` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
                    `USERNAME` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
                    `TIMESTAMP_X` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (`TR_ID`),
                    KEY `tr_id_users_bx_id_idx` (`BX_ID`)
                    ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
        $DB->Query($sql);

	    // Add index to field BX_ID in existing table tr_id_users created in far past
	    if (!$DB->IndexExists("tr_id_users", array("BX_ID"))) {
		    $sql = "CREATE INDEX tr_id_users_bx_id_idx ON tr_id_users(BX_ID)";
		    $DB->Query($sql);
	    }
    }

    function UnInstallDB()
    {
        global $DB;
        $sql = "DROP TABLE IF EXISTS `tr_id_users`";
        $DB->Query($sql);
    }

    function InstallFiles()
    {
        CopyDirFiles(
            $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/' . self::MODULE_ID . '/install/components/',
            $_SERVER['DOCUMENT_ROOT'] . '/bitrix/components/',
            true, true
        );
        CopyDirFiles(
            $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/' . self::MODULE_ID . '/install/admin',
            $_SERVER['DOCUMENT_ROOT'] . '/bitrix/admin',
            true, false
        );
        CopyDirFiles(
            $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/' . self::MODULE_ID . '/install/themes',
            $_SERVER['DOCUMENT_ROOT'] . '/bitrix/themes',
            true, true
        );
        return true;
    }

    function UnInstallFiles()
    {
        DeleteDirFilesEx('/bitrix/components/trusted/id/');
        DeleteDirFiles(
            $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/' . self::MODULE_ID . '/install/admin/',
            $_SERVER['DOCUMENT_ROOT'] . '/bitrix/admin'
        );
        DeleteDirFiles(
            $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/' . self::MODULE_ID . '/install/themes/.default/',
            $_SERVER['DOCUMENT_ROOT'] . '/bitrix/themes/.default/'
        );
        DeleteDirFilesEx('/bitrix/themes/.default/icons/' . self::MODULE_ID);
        return true;
    }

    function LogOutTrustedUser()
    {
        if (isset($_SESSION['TRUSTEDNET']['OAUTH'])) {
            unset($_SESSION['TRUSTEDNET']['OAUTH']);
        }
    }
}

