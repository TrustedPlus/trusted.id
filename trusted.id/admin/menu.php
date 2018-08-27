<?php
if ($APPLICATION->GetGroupRight("trusted.id") >= "R") {

    if (\Bitrix\Main\ModuleManager::isModuleInstalled('trusted.id')) {
        IncludeModuleLangFile(__FILE__);
        $aMenu = array(
            "parent_menu" => "global_menu_settings",
            "section" => "trusted.id",
            "sort" => 20,
            "text" => GetMessage("TR_ID_MENU_SERVICE"),
            "title" => GetMessage("TR_ID_MENU_SERVICE_ALT"),
            "icon" => "trusted_id_menu_icon",
            "page_icon" => "trusted_id_page_icon",
            "items_id" => "menu_trusted.id",
            "items" => array()
        );


        $Menu[] = array("text" => GetMessage("TR_ID_MENU_USERS"),
            "url" => "trusted_id_users.php?lang=" . LANGUAGE_ID,
            // Subpages
            // "more_url" => array("trusted_id_docs_loading.php"),
            "title" => GetMessage("TR_ID_MENU_USERS_ALT")
        );

        $aMenu["items"] = $Menu;
        return $aMenu;
    }
}
return false;

