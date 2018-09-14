<?php

use Bitrix\Main\Config\Option;
use Trusted\Id;

Class trusted_id extends CModule
{

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

        include substr(__FILE__, 0, -10) . '/version.php';

        $this->MODULE_ID = 'trusted.id';
        $this->MODULE_NAME = GetMessage('TR_ID_MODULE_NAME');
        $this->MODULE_DESCRIPTION = GetMessage('TR_ID_MODULE_DESCRIPTION');
        $this->MODULE_VERSION = '1.3.2';
        $this->MODULE_VERSION_DATE = '2018-04-20 9:00:00';
        $this->PARTNER_NAME = GetMessage('TR_ID_PARTNER_NAME');
        $this->PARTNER_URI = 'http://www.digt.ru';
    }

    function RegisterEventHandlers()
    {
        RegisterModuleDependences('main', 'OnAfterUserAdd', $this->MODULE_ID, '\Trusted\Id\Auth', 'OnAfterUserAddHandler');
        RegisterModuleDependences('main', 'OnAfterUserRegister', $this->MODULE_ID, '\Trusted\Id\Auth', 'OnAfterUserRegisterHandler');
        RegisterModuleDependences('main', 'OnAfterUserSimpleRegister', $this->MODULE_ID, '\Trusted\Id\Auth', 'OnAfterUserSimpleRegisterHandler');
        RegisterModuleDependences('main', 'OnBeforeUserUpdate', $this->MODULE_ID, '\Trusted\Id\Auth', 'OnBeforeUserUpdateHandler');
        RegisterModuleDependences('main', 'OnBeforeUserAdd', $this->MODULE_ID, '\Trusted\Id\Auth', 'OnBeforeUserAddHandler');
        RegisterModuleDependences('main', 'OnBeforeEventSend', $this->MODULE_ID, '\Trusted\Id\Auth', 'OnBeforeEventSendHandler');
        RegisterModuleDependences('main', 'OnUserLogin', $this->MODULE_ID, '\Trusted\Id\Auth', 'OnUserLoginHandler');

        RegisterModuleDependences('sale', 'OnSaleComponentOrderOneStepComplete', $this->MODULE_ID, 'Id\Auth', 'OnSaleComponentOrderOneStepCompleteHandler');
    }

    function UnRegisterEventHandlers()
    {
        UnRegisterModuleDependences('main', 'OnAfterUserAdd', $this->MODULE_ID, '\Trusted\Id\Auth');
        UnRegisterModuleDependences('main', 'OnAfterUserRegister', $this->MODULE_ID, '\Trusted\Id\Auth');
        UnRegisterModuleDependences('main', 'OnAfterUserSimpleRegister', $this->MODULE_ID, '\Trusted\Id\Auth');
        UnRegisterModuleDependences('main', 'OnBeforeUserUpdate', $this->MODULE_ID, '\Trusted\Id\Auth');
        UnRegisterModuleDependences('main', 'OnBeforeUserAdd', $this->MODULE_ID, '\Trusted\Id\Auth');
        UnRegisterModuleDependences('main', 'OnBeforeEventSend', $this->MODULE_ID, '\Trusted\Id\Auth');
        UnRegisterModuleDependences('main', 'OnUserLogin', $this->MODULE_ID, '\Trusted\Id\Auth');

        UnRegisterModuleDependences('sale', 'OnSaleComponentOrderOneStepComplete', $this->MODULE_ID, '\Trusted\Id\Auth');
    }

    function DoInstall()
    {
        global $DOCUMENT_ROOT, $APPLICATION;
        $this->InstallFiles();
        $this->InstallDB();
        $this->RegisterEventHandlers();
        RegisterModule($this->MODULE_ID);
        $APPLICATION->IncludeAdminFile(GetMessage('MOD_INSTALL_TITLE'), $DOCUMENT_ROOT . '/bitrix/modules/' . $this->MODULE_ID . '/install/step.php');
    }

    function DoUninstall()
    {
        global $DB, $APPLICATION, $step;
        $this->LogOutTrustedUser();
        $this->UnInstallFiles();
        //$this->UnInstallDB();
        $this->UnRegisterEventHandlers();
        UnRegisterModule($this->MODULE_ID);
        $APPLICATION->IncludeAdminFile(GetMessage('MOD_INSTALL_TITLE'), $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/' . $this->MODULE_ID . '/install/unstep.php');
    }

    function InstallDB()
    {
        global $DB;
        $sql = "CREATE TABLE IF NOT EXISTS `tr_id_users` (
                    `ID` int(11) NOT NULL,
                    `USER_ID` int(18) DEFAULT NULL,
                    `GIVEN_NAME` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
                    `FAMILY_NAME` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
                    `EMAIL` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
                    `TIMESTAMP_X` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (`ID`)
                    ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
        $DB->Query($sql);
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
            $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/' . $this->MODULE_ID . '/install/components/',
            $_SERVER['DOCUMENT_ROOT'] . '/bitrix/components/',
            true, true
        );
        CopyDirFiles(
            $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/' . $this->MODULE_ID . '/install/admin',
            $_SERVER['DOCUMENT_ROOT'] . '/bitrix/admin',
            true, false
        );
        CopyDirFiles(
            $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/' . $this->MODULE_ID . '/install/themes',
            $_SERVER['DOCUMENT_ROOT'] . '/bitrix/themes',
            true, true
        );
        return true;
    }

    function UnInstallFiles()
    {
        DeleteDirFilesEx('/bitrix/components/trusted/id/');
        DeleteDirFiles(
            $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/' . $this->MODULE_ID . '/install/admin/',
            $_SERVER['DOCUMENT_ROOT'] . '/bitrix/admin'
        );
        DeleteDirFiles(
            $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/' . $this->MODULE_ID . '/install/themes/.default/',
            $_SERVER['DOCUMENT_ROOT'] . '/bitrix/themes/.default/'
        );
        DeleteDirFilesEx('/bitrix/themes/.default/icons/' . $this->MODULE_ID);
        return true;
    }

    function LogOutTrustedUser()
    {
        if (isset($_SESSION['TRUSTEDNET']['OAUTH'])) {
            unset($_SESSION['TRUSTEDNET']['OAUTH']);
        }
    }
}

