<?php
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

CModule::IncludeModule("trustednet.auth");

$APPLICATION->SetTitle(GetMessage("TRUSTEDNET_USERS_PAGE_TITLE"));

$POST_RIGHT = $APPLICATION->GetGroupRight("trustednet.auth");
if ($POST_RIGHT == "D") {
    $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
}

$sTableID = "bindingTable";
$oSort = new CAdminSorting($sTableID, "ID", "asc");
$lAdmin = new CAdminList($sTableID, $oSort);

// Filter elements ids
$FilterArr = Array(
    "find_id",
    "find_login",
    "find_name",
    "find_last_name",
    "find_email",
    "find_tn_id",
);

$lAdmin->InitFilter($FilterArr);

// Filtration array for GetList
$arFilter = Array(
    "ID" => $find_id,
    "LOGIN" => $find_login,
    "NAME" => $find_name,
    "LAST_NAME" => $find_last_name,
    "EMAIL" => $find_email,
    "TN_ID" => $find_tn_id,
);

// Saves edited elements
if($lAdmin->EditAction() && $POST_RIGHT=="W") {
    foreach($FIELDS as $ID=>$arFields) {
        if(!$lAdmin->IsUpdated($ID))
            continue;

        $DB->StartTransaction();
        $ID = IntVal($ID);
        $cData = new CRubric;
        if (($rsData = $cData->GetByID($ID)) && ($arData = $rsData->Fetch())) {
            foreach($arFields as $key=>$value) {
                $arData[$key]=$value;
            }
            if(!$cData->Update($ID, $arData)) {
                $lAdmin->AddGroupError(GetMessage("rub_save_error")." ".$cData->LAST_ERROR, $ID);
                $DB->Rollback();
            }
        } else {
            $lAdmin->AddGroupError(GetMessage("rub_save_error")." ".GetMessage("rub_no_rubric"), $ID);
            $DB->Rollback();
        }
        $DB->Commit();
    }
}

// Hadnle actions
if(($arID = $lAdmin->GroupAction()) && $POST_RIGHT=="W") {
    // selected = for all
    if($_REQUEST['action_target']=='selected')
    {
        $cData = new CRubric;
        $rsData = $cData->GetList(array($by=>$order), $arFilter);
        while($arRes = $rsData->Fetch())
            $arID[] = $arRes['ID'];
    }

    foreach($arID as $ID) {
        if(strlen($ID)<=0)
            continue;
        $ID = IntVal($ID);

        switch($_REQUEST['action']) {
            case "delete":
                @set_time_limit(0);
                $DB->StartTransaction();
                if(!CRubric::Delete($ID)) {
                    $DB->Rollback();
                    $lAdmin->AddGroupError(GetMessage("rub_del_err"), $ID);
                }
                $DB->Commit();
                break;
            case "activate":
            case "deactivate":
                $cData = new CRubric;
                if(($rsData = $cData->GetByID($ID)) && ($arFields = $rsData->Fetch())) {
                    $arFields["ACTIVE"]=($_REQUEST['action']=="activate"?"Y":"N");
                    if(!$cData->Update($ID, $arFields))
                        $lAdmin->AddGroupError(GetMessage("rub_save_error").$cData->LAST_ERROR, $ID);
                } else {
                    $lAdmin->AddGroupError(GetMessage("rub_save_error")." ".GetMessage("rub_no_rubric"), $ID);
                }
                break;
        }
    }
}

// Get list of users with filter applied
$rsData = CUser::GetList($by, $order, $arFilter);

// Take apart GetList result to insert new values
$TrustedAuth = new TrustedAuth;
$arData = array();
while ($elem = $rsData->Fetch()) {
    // Add TN_ID column to the results
    $tn_id = $TrustedAuth->getUserRowByUserId($elem["ID"]);
    $tn_id = $tn_id["data"]["ID"];
    $tn_id ? $elem["TN_ID"] = $tn_id : $elem["TN_ID"] = "Отсутствует";
    // Manually apply filter for inserted values
    if ($find_tn_id) {
        if (strstr($tn_id, $find_tn_id) !== false) {
            $arData[] = $elem;
        }
    } else {
        $arData[] = $elem;
    }
}

// Manually apply sorting for inserted values
if ($_GET["by"] == "tn_id") {
    $tn_id_multisort = array();
    foreach ($arData as $elem) {
        $tn_id_multisort[] = $elem["TN_ID"];
    }
    if ($_GET["order"] == "asc") {
        array_multisort($tn_id_multisort, SORT_ASC, $arData);
    }
    if ($_GET["order"] == "desc") {
        array_multisort($tn_id_multisort, SORT_DESC, $arData);
    }
}

// Assemble query result back again with the new values,
// filter and sorting
$rsData = new CDBResult;
$rsData->InitFromArray($arData);

// Convert to table data
$rsData = new CAdminResult($rsData, $sTableID);

// Enable pagination
$rsData->NavStart();

// Add page switcher to the main object
$lAdmin->NavText($rsData->GetNavPrint(GetMessage("TRUSTEDNET_USERS_NAV_TEXT")));

$lAdmin->AddHeaders(
    array(
        array(
            "id" => "ID",
            "content" => GetMessage("TRUSTEDNET_USERS_COL_ID"),
            "sort" => "id",
            "default" => true,
        ),
        array(
            "id" => "LOGIN",
            "content" => GetMessage("TRUSTEDNET_USERS_COL_LOGIN"),
            "sort" => "login",
            "default" => true,
        ),
        array(
            "id" => "NAME",
            "content" => GetMessage("TRUSTEDNET_USERS_COL_NAME"),
            "sort" => "name",
            "default" => true,
        ),
        array(
            "id" => "LAST_NAME",
            "content" => GetMessage("TRUSTEDNET_USERS_COL_LAST_NAME"),
            "sort" => "last_name",
            "default" => true,
        ),
        array(
            "id" => "EMAIL",
            "content" => GetMessage("TRUSTEDNET_USERS_COL_EMAIL"),
            "sort" => "email",
            "default" => true,
        ),
        array(
            "id" => "TN_ID",
            "content" => GetMessage("TRUSTEDNET_USERS_COL_TN_ID"),
            "sort" => "tn_id",
            "default" => true,
        ),
    )
);

while($arRes = $rsData->NavNext(true, "f_")) {
    // Create a row, class CAdminListRow
    $row =& $lAdmin->AddRow($f_ID, $arRes);

    // Some fields can be edited
    $row->AddViewField("LOGIN", '<a href="user_edit.php?ID='.$f_ID.'&lang='.LANG.'">'.$f_LOGIN.'</a>');

    // Context menu
    $arActions = Array();

    $arActions[] = array(
        "ICON" => "edit",
        "DEFAULT" => true,
        "TEXT" => "EDIT BITRIX USER",
        "ACTION" => $lAdmin->ActionRedirect("user_edit.php?ID=".$f_ID),
    );

    $arActions[] = array("SEPARATOR" => true);

    $arActions[] = array(
        "ICON" => "delete",
        "TEXT" => "DELETE",
        "ACTION" =>"if(confirm('CONFIRM')) " . $lAdmin->ActionDoGroup($f_ID, "delete")
    );

    // Apply context menu to the row
    $row->AddActions($arActions);
}

//$lAdmin->AddFooter(
//    array(
//        array(
//            "title" => "SELECTED TEXT",
//            "value" => $rsData->SelectedRowsCount()
//        ), // Number of elements
//        array(
//            "counter" => true,
//            "title" => "CHECKED TEXT",
//            "value" => "0"
//        ), // Selected elem counter
//    )
//);

$lAdmin->AddGroupActionTable(Array(
    "delete"=>GetMessage("MAIN_ADMIN_LIST_DELETE"),
    "activate"=>GetMessage("MAIN_ADMIN_LIST_ACTIVATE"),
    "deactivate"=>GetMessage("MAIN_ADMIN_LIST_DEACTIVATE"),
));

// Buttons
//$aContext = array(
//    array(
//        "TEXT"=>"BUTTON",
//        "LINK"=>"rubric_edit.php?lang=".LANG,
//        "TITLE"=>"BUTTON TEXT",
//        "ICON"=>"btn_new",
//    ),
//);
//
//$lAdmin->AddAdminContextMenu($aContext);

// Start output in AJAX or Excel table if needed
$lAdmin->CheckListMode();

// Separates preparing of data and output
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$oFilter = new CAdminFilter(
    $sTableID."_filter",
    array(
        GetMessage("TRUSTEDNET_USERS_COL_LOGIN"),
        GetMessage("TRUSTEDNET_USERS_COL_NAME"),
        GetMessage("TRUSTEDNET_USERS_COL_LAST_NAME"),
        GetMessage("TRUSTEDNET_USERS_COL_EMAIL"),
        GetMessage("TRUSTEDNET_USERS_COL_TN_ID"),
    )
);

//require_once $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/trustednet.auth/install/components/trustednet/trustednet.auth/oauth2.php";
$APPLICATION->IncludeComponent("trustednet:trustednet.auth", array());

$auth = OAuth2::getFromSession();
if ($auth) {
?>

    <form name="find_form" method="get" action="<?echo $APPLICATION->GetCurPage();?>">

    <?$oFilter->Begin();?>

    <? //TODO: add id filter form ?>
    <tr>
        <td><?= GetMessage("TRUSTEDNET_USERS_COL_LOGIN") ?></td>
        <td>
            <input type="text" size="25" name="find_login" value="<?echo htmlspecialchars($find_login)?>">
        </td>
    </tr>

    <tr>
        <td><?= GetMessage("TRUSTEDNET_USERS_COL_NAME") ?></td>
        <td>
            <input type="text" name="find_name" size="47" value="<?echo htmlspecialchars($find_name)?>">
        </td>
    </tr>

    <tr>
        <td><?= GetMessage("TRUSTEDNET_USERS_COL_LAST_NAME") ?></td>
        <td>
            <input type="text" name="find_last_name" size="47" value="<?echo htmlspecialchars($find_last_name)?>">
        </td>
    </tr>

    <tr>
        <td><?= GetMessage("TRUSTEDNET_USERS_COL_EMAIL") ?></td>
        <td>
            <input type="text" name="find_email" size="47" value="<?echo htmlspecialchars($find_email)?>">
        </td>
    </tr>

    <tr>
        <td><?= GetMessage("TRUSTEDNET_USERS_COL_TN_ID") ?></td>
        <td>
            <input type="text" name="find_tn_id" size="47" value="<?echo htmlspecialchars($find_tn_id)?>">
        </td>
    </tr>

    <?
    $oFilter->Buttons(array("table_id"=>$sTableID,"url"=>$APPLICATION->GetCurPage(),"form"=>"find_form"));
    $oFilter->End();
    ?>
    </form>

    <? $lAdmin->DisplayList(); ?>
<?
}
?>

<?php require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_admin.php"); ?>

