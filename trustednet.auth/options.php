<?php
IncludeModuleLangFile(__FILE__);
require_once(__DIR__ . "/classes/config.php");

$module_id = TN_AUTH_MODULE_ID;

$aTabs = array(
    array(
        "DIV" => "edit0",
        "TAB" => GetMessage("TN_AUTH_OPTIONS_TAB_SETTINGS"),
        "TITLE" => GetMessage("TN_AUTH_OPTIONS_TAB_SETTINGS_TITLE")
    ),
);

$tabControl = new CAdminTabControl("trustedTabControl", $aTabs, true, true);

$CLIENT_ID = COption::GetOptionString($module_id, "CLIENT_ID", "");
$CLIENT_SECRET = COption::GetOptionString($module_id, "CLIENT_SECRET", "");
$REGISTER_ENABLED = COption::GetOptionString($module_id, "REGISTER_ENABLED", "");
$USER_INFO_TEMPLATE_ID = COption::GetOptionString($module_id, "USER_INFO_TEMPLATE_ID", "");
$SEND_MAIL_ENABLED = TN_DEFAULT_SHOULD_SEND_MAIL;
$REDIRECT_URL = COption::GetOptionString($module_id, "REDIRECT_URL", "");

function CheckRedirectUrl($url)
{
    $res = true;
    if ($url == "")
        return true;
    if (!filter_var($url, FILTER_VALIDATE_URL))
        $res = false;
    if (!preg_match("/^(http:\/\/|https:\/\/).*/", $url))
        $res = false;
    return $res;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && check_bitrix_sessid()) {
    if (isset($_POST['Update'])) {
        if (isset($_POST['CLIENT_ID']))
            $CLIENT_ID = (string)$_POST['CLIENT_ID'];
        if ($CLIENT_ID != '') {
            COption::SetOptionString($module_id, "CLIENT_ID", $CLIENT_ID);
        }

        if (isset($_POST['CLIENT_SECRET']))
            $CLIENT_SECRET = (string)$_POST['CLIENT_SECRET'];
        if ($CLIENT_SECRET != '') {
            COption::SetOptionString($module_id, "CLIENT_SECRET", $CLIENT_SECRET);
        }

        $REGISTER_ENABLED = isset($_POST['REGISTER_ENABLED']) && (string)$_POST['REGISTER_ENABLED'] == 'on';

        if ($REGISTER_ENABLED) {
            if (isset($_POST['USER_INFO_TEMPLATE_ID'])) {
                $USER_INFO_TEMPLATE_ID = $_POST['USER_INFO_TEMPLATE_ID'];
            }
            $SEND_MAIL_ENABLED = isset($_POST['SEND_MAIL_ENABLED']) && (string)$_POST['SEND_MAIL_ENABLED'] == 'on';
        }

        if (isset($_POST['REDIRECT_URL'])) {
            $REDIRECT_URL_POST = (string)$_POST['REDIRECT_URL'];
            if (CheckRedirectUrl($REDIRECT_URL_POST)) {
                $REDIRECT_URL = $REDIRECT_URL_POST;
                COption::SetOptionString($module_id, "REDIRECT_URL", $REDIRECT_URL_POST);
            } else {
                CAdminMessage::ShowMessage(GetMessage("TN_AUTH_REDIRECT_URL_INVALID"));
            }
        }

        COption::SetOptionString($module_id, "REGISTER_ENABLED", $REGISTER_ENABLED);
        COption::SetOptionString($module_id, 'USER_INFO_TEMPLATE_ID', $USER_INFO_TEMPLATE_ID);
        COption::SetOptionString($module_id, 'SEND_MAIL_ENABLED', $SEND_MAIL_ENABLED);
    }
}

?>
<? if (COption::GetOptionString("main", "new_user_email_uniq_check") === "Y") {
    $tabControl->Begin();
    $tabControl->BeginNextTab();
    ?>

    <div style="margin-bottom: 40px; position:relative">
        <h3>
            <?= GetMessage("TN_EMAIL_UNIQ_CHECK_TITLE") ?>!
        </h3>
        <div>
            <?= GetMessage("TN_EMAIL_UNIQ_CHECK_PREFIX") ?>
            "<i><b><?= GetMessage("TN_MAIN_REGISTER_EMAIL_UNIQ_CHECK_RU") ?></b></i>"
            <?= GetMessage("TN_EMAIL_UNIQ_CHECK_POSTFIX") ?>
        </div>
        <div style="position:absolute; right: 10px; top: -10px">
            <a href="https://net.trusted.ru" target="_blank"><?= GetMessage("TN_SERVICE_LINK") ?></a>
        </div>
    </div>

    <form method="POST" action="<? echo $APPLICATION->GetCurPage() ?>?lang=<? echo LANGUAGE_ID ?>&mid=<?= $module_id ?>"
          name="currency_settings">
        <? echo bitrix_sessid_post(); ?>
        <tr>
            <td width="40%" class="adm-detail-content-cell-l"><?= GetMessage("TN_AUTH_CLIENT_ID") ?></td>
            <td width="60%"><input name="CLIENT_ID" value="<?= $CLIENT_ID ?>"/></td>
        </tr>
        <tr>
            <td width="40%" class="adm-detail-content-cell-l"><?= GetMessage("TN_AUTH_CLIENT_SECRET") ?></td>
            <td width="60%"><input name="CLIENT_SECRET" value="<?= $CLIENT_SECRET ?>"/></td>
        </tr>
        <tr>
            <td class="adm-detail-content-cell-l">
                <input type="checkbox" <? echo($REGISTER_ENABLED ? "checked='checked'" : "") ?>
                       id="autoRegister"
                       name="REGISTER_ENABLED"/>
            </td>
            <td>
                <label for="autoRegister"><?= GetMessage("TN_AUTH_ENABLE_AUTO_REGISTRATION") ?></label>
            </td>
        </tr>
        <?php
        if (TN_USE_SEND_MAIL_SETTINGS) {
            ?>
            <tr>
                <td class="adm-detail-content-cell-l">
                    <input type="checkbox"
                        <?= (($SEND_MAIL_ENABLED) ? "checked='checked'" : "") ?>
                        <?= $REGISTER_ENABLED ? "" : "disabled='disabled'" ?>
                           name="SEND_MAIL_ENABLED"/>
                </td>
                <td>
                    <label for="autoRegister"><?= GetMessage("TN_AUTH_ENABLE_SEND_MAIL") ?></label>
                </td>
            </tr>
            <?
        }
        ?>
        <tr>
            <td class="adm-detail-content-cell-l"><?= GetMessage("TN_AUTH_USER_INFO_TEMPLATE_ID") ?></td>
            <td><input name="USER_INFO_TEMPLATE_ID"
                    <?= $REGISTER_ENABLED ? "" : "disabled='disabled'" ?>
                       type="number"
                       min="1"
                       max="999"
                       value="<?= $USER_INFO_TEMPLATE_ID ?>"/></td>
        </tr>
        <tr>
            <td width="40%" class="adm-detail-content-cell-l"><?= GetMessage("TN_AUTH_REDIRECT_URL") ?></td>
            <td width="60%">
                <input name="REDIRECT_URL" style="width: 400px;" value="<?= $REDIRECT_URL ?>"/>
            </td>
        </tr>
        <? $tabControl->Buttons(); ?>

        <input type="submit" name="Update" value="<?= GetMessage("TN_SETTINGS_SAVE") ?>"/>
    </form>

    <?
} else {
    ?>
    <h3 style="margin-bottom: 10px;">
        <?= GetMessage("TN_SET_EMAIL_UNIQ_CHECK_PREFIX") ?>
        </br>"<i><?= GetMessage("TN_MAIN_REGISTER_EMAIL_UNIQ_CHECK_RU") ?></i>"</br>
        <a href="/bitrix/admin/settings.php?lang=ru&mid=main&tabControl_active_tab=edit6#opt_new_user_registration_email_confirmation">
            <?= GetMessage("TN_SET_EMAIL_UNIQ_CHECK_POSTFIX") ?>
        </a>
    </h3>
    <?
}

