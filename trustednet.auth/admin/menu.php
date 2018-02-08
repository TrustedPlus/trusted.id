<?php
if ($APPLICATION->GetGroupRight("trustednet.auth") >= "R") {

    if (\Bitrix\Main\ModuleManager::isModuleInstalled('trustednet.auth')) {
        IncludeModuleLangFile(__FILE__);
        $aMenu = array(
            "parent_menu" => "global_menu_settings",
            "section" => "trustednet",
            "sort" => 20,
            "text" => GetMessage("TRUSTEDNET_S"),
            "title" => GetMessage("TRUSTEDNET_S_ALT"),
            "icon" => "trustednetauth_menu_icon",
            "page_icon" => "trustednetauth_page_icon",
            "items_id" => "menu_trustednet.auth",
            "items" => array()
        );


        $Menu[] = array("text" => GetMessage("TRUSTEDNET_S_MENU_USERS"),
            "url" => "trustednet_users.php?lang=" . LANGUAGE_ID,
            // Subpages
            // "more_url" => array("trustednet_docs_loading.php"),
            "title" => GetMessage("TRUSTEDNET_S_MENU_USERS_ALT")
        );

        $aMenu["items"] = $Menu;
        return $aMenu;
    }
}
return false;

