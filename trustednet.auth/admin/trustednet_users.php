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

$TrustedAuth = new TrustedAuth;

// Filter elements ids
$FilterArr = array(
    "find_id",
    "find_login",
    "find_name",
    "find_email",
    "find_tn_id",
    "find_tn_giv_name",
    "find_tn_fam_name",
    "find_tn_email",
);

$lAdmin->InitFilter($FilterArr);

// Filtration array for GetList
$arFilter = array(
    "ID" => $find_id,
    "LOGIN" => $find_login,
    "NAME" => $find_name,
    "EMAIL" => $find_email,
    "TN_ID" => $find_tn_id,
    "TN_GIV_NAME" => $find_tn_giv_name,
    "TN_FAM_NAME" => $find_tn_fam_name,
    "TN_EMAIL" => $find_tn_email,
);

// Handle edits
if($lAdmin->EditAction() && $POST_RIGHT=="W") {
    foreach($FIELDS as $userId=>$editedFields) {
        $newTnId = $editedFields["TN_ID"];
        if (!is_numeric($newTnId)) {
            echo BeginNote();
            echo GetMessage("TN_NON_NUMERIC_ID_PRE") . $newTnId;
            echo GetMessage("TN_NON_NUMERIC_ID_POST");
            echo EndNote();
            break;
        }
        $newTnId = (int)$newTnId;
        if (TDataBaseUser::getUserById($newTnId)) {
            echo BeginNote();
            echo GetMessage("TN_BINDING_EXISTS_PRE") . $newTnId;
            echo GetMessage("TN_BINDING_EXISTS_POST");
            echo EndNote();
            break;
        }
        $token = OAuth2::getFromSession();
        if (!$token) {
            break;
        }
        $token = $token->getAccessToken();
        $tnUserInfo = TAuthCommand::pullTnInfo($token, "id", $newTnId);
        if (!$tnUserInfo) {
            echo BeginNote();
            echo GetMessage("TN_USER_NOT_FOUND_PRE") . $newTnId;
            echo GetMessage("TN_USER_NOT_FOUND_POST");
            echo EndNote();
            break;
        }
        $TrustedAuth->bindUsers(
            array("ID" => $userId),
            array("userID" => $newTnId)
        );
    }
}

// Handle actions
if(($arID = $lAdmin->GroupAction()) && $POST_RIGHT=="W") {
    // selected = for all
    if($_REQUEST['action_target']=='selected') {
        $rsData = CUser::GetList($by, $order, $arFilter);
        while($arRes = $rsData->Fetch()) {
            $arID[] = $arRes['ID'];
        }
    }

    foreach($arID as $ID) {
        if(strlen($ID)<=0)
            continue;
        $ID = IntVal($ID);

        switch($_REQUEST['action']) {
            case "pull_tn_info":
                $token = OAuth2::getFromSession();
                if (!$token) {
                    break;
                }
                $token = $token->getAccessToken();
                $bxUser = CUser::GetById($ID);
                $bxUser = $bxUser->Fetch();
                $tnUserInfo = TAuthCommand::pullTnInfo($token, "email", $bxUser["EMAIL"]);
                if ($tnUserInfo) {
                    TDataBaseUser::removeUserByUserId($ID);
                    $serviceUser = ServiceUser::fromArray($tnUserInfo);
                    $user = new TUser();
                    $user->setServiceUser($serviceUser);
                    $user->setUserId($ID);
                    $user->save();
                }
                break;
            case "register":
                $bxUser = CUser::GetById($ID);
                $bxUser = $bxUser->Fetch();
                $bxUser["RESULT"] = true;
                $TrustedAuth->registerUser($bxUser, true);
                break;
            case "remove":
                TDataBaseUser::removeUserByUserId($ID);
                break;
        }
    }
}

// Get list of users with filter applied
$TDataBaseUser = new TDataBaseUser;
$users = $TDataBaseUser->getBitrixAndTnUsers($by, $order, $arFilter);
$rsData = new CAdminResult($users, $sTableID);

// Enable pagination
$rsData->NavStart();
// Hide "Show all on one page" in pagination footer
$rsData->bShowAll = 0;

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
        array(
            "id" => "TN_GIV_NAME",
            "content" => GetMessage("TRUSTEDNET_USERS_COL_TN_GIV_NAME"),
            "sort" => "tn_giv_name",
            "default" => true,
        ),
        array(
            "id" => "TN_FAM_NAME",
            "content" => GetMessage("TRUSTEDNET_USERS_COL_TN_FAM_NAME"),
            "sort" => "tn_fam_name",
            "default" => true,
        ),
        array(
            "id" => "TN_EMAIL",
            "content" => GetMessage("TRUSTEDNET_USERS_COL_TN_EMAIL"),
            "sort" => "tn_email",
            "default" => true,
        ),
    )
);

while($arRes = $rsData->NavNext(true, "f_")) {
    // Create a row, class CAdminListRow
    $row =& $lAdmin->AddRow($f_ID, $arRes);

    // Some fields can be edited
    $row->AddViewField("LOGIN", '<a href="user_edit.php?ID='.$f_ID.'&lang='.LANG.'">'.$f_LOGIN.'</a>');
    $row->AddInputField("TN_ID");

    // Context menu
    $arActions = Array();

    $arActions[] = array(
        "ICON" => "view",
        "DEFAULT" => true,
        "TEXT" => GetMessage("TRUSTEDNET_USERS_ACT_PULL_TN_INFO"),
        "ACTION" => $lAdmin->ActionDoGroup($f_ID, "pull_tn_info"),
    );

    $arActions[] = array("SEPARATOR" => true);

    $arActions[] = array(
        "ICON" => "edit",
        "DEFAULT" => true,
        "TEXT" => GetMessage("TRUSTEDNET_USERS_ACT_REGISTER"),
        "ACTION" => $lAdmin->ActionDoGroup($f_ID, "register"),
    );

    if ($f_TN_ID) {
        $arActions[] = array("SEPARATOR" => true);

        $arActions[] = array(
            "ICON" => "delete",
            "DEFAULT" => true,
            "TEXT" => GetMessage("TRUSTEDNET_USERS_ACT_REMOVE"),
            "ACTION" => $lAdmin->ActionDoGroup($f_ID, "remove"),
        );
    }

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

$lAdmin->AddGroupActionTable(
    array(
        "pull_tn_info" => GetMessage("TRUSTEDNET_USERS_ACT_PULL_TN_INFO"),
        "register" => GetMessage("TRUSTEDNET_USERS_ACT_REGISTER"),
        "remove" => GetMessage("TRUSTEDNET_USERS_ACT_REMOVE"),
    )
);

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
        GetMessage("TRUSTEDNET_USERS_COL_ID"),
        GetMessage("TRUSTEDNET_USERS_COL_LOGIN"),
        GetMessage("TRUSTEDNET_USERS_COL_NAME"),
        GetMessage("TRUSTEDNET_USERS_COL_EMAIL"),
        GetMessage("TRUSTEDNET_USERS_COL_TN_ID"),
        GetMessage("TRUSTEDNET_USERS_COL_TN_GIV_NAME"),
        GetMessage("TRUSTEDNET_USERS_COL_TN_FAM_NAME"),
        GetMessage("TRUSTEDNET_USERS_COL_TN_EMAIL"),
    )
);

if (!isSecure()) {
    echo BeginNote(), GetMessage("TRUSTEDNET_HTTP_WARNING"), EndNote();
}

if (!checkCurl()) {
    echo BeginNote(), GetMessage("TRUSTEDNET_CURL_WARNING"), EndNote();
} elseif (COption::GetOptionString("main", "new_user_email_uniq_check") !== "Y") {
?>
    <h3 style="margin-bottom: 10px;">
        <?= GetMessage("TN_SET_EMAIL_UNIQ_CHECK_PREFIX") ?>
        </br>"<i><?= GetMessage("TN_MAIN_REGISTER_EMAIL_UNIQ_CHECK_RU") ?></i>"</br>
        <a href="/bitrix/admin/settings.php?lang=ru&mid=main&tabControl_active_tab=edit6#opt_new_user_registration_email_confirmation">
            <?= GetMessage("TN_SET_EMAIL_UNIQ_CHECK_POSTFIX") ?>
        </a>
    </h3>
<?

} elseif (!OAuth2::getFromSession()) {
    $APPLICATION->IncludeComponent("trustednet:trustednet.auth", array());
} else {
    $auth = OAuth2::getFromSession();
?>

    <form name="find_form" method="get" action="<?echo $APPLICATION->GetCurPage();?>">

    <?$oFilter->Begin();?>

    <tr>
        <td><?= GetMessage("TRUSTEDNET_USERS_COL_ID") ?></td>
        <td>
            <input type="text" size="25" name="find_id" value="<?echo htmlspecialchars($find_id)?>">
        </td>
    </tr>

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

    <tr>
        <td><?= GetMessage("TRUSTEDNET_USERS_COL_TN_GIV_NAME") ?></td>
        <td>
            <input type="text" name="find_tn_giv_name" size="47" value="<?echo htmlspecialchars($find_tn_giv_name)?>">
        </td>
    </tr>

    <tr>
        <td><?= GetMessage("TRUSTEDNET_USERS_COL_TN_FAM_NAME") ?></td>
        <td>
            <input type="text" name="find_tn_fam_name" size="47" value="<?echo htmlspecialchars($find_tn_fam_name)?>">
        </td>
    </tr>

    <tr>
        <td><?= GetMessage("TRUSTEDNET_USERS_COL_TN_EMAIL") ?></td>
        <td>
            <input type="text" name="find_tn_email" size="47" value="<?echo htmlspecialchars($find_tn_email)?>">
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

