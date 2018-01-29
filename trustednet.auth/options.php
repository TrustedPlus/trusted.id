<?php
require_once __DIR__ . "/classes/config.php";
$module_id = TN_AUTH_MODULE_ID;
IncludeModuleLangFile(__FILE__);

$aTabs = array(
    array(
        "DIV" => "tab_settings",
        "TAB" => GetMessage("TN_AUTH_OPTIONS_TAB_SETTINGS"),
        "TITLE" => GetMessage("TN_AUTH_OPTIONS_TAB_SETTINGS_TITLE")
    ),
    array(
        "DIV" => "tab_info",
        "TAB" => GetMessage("TN_AUTH_OPTIONS_TAB_INFO"),
        "TITLE" => GetMessage("TN_AUTH_OPTIONS_TAB_INFO_TITLE")
    ),
);

$tabControl = new CAdminTabControl("trustedTabControl", $aTabs, true, true);

$CLIENT_ID = COption::GetOptionString($module_id, "CLIENT_ID", "");
$CLIENT_SECRET = COption::GetOptionString($module_id, "CLIENT_SECRET", "");
$REGISTER_ENABLED = COption::GetOptionString($module_id, "REGISTER_ENABLED", "");
$USER_INFO_TEMPLATE_ID = COption::GetOptionString($module_id, "USER_INFO_TEMPLATE_ID", "2");
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
        if (isset($_POST['CLIENT_ID'])) {
            if ($_POST['CLIENT_ID'] != $CLIENT_ID) {
                $CLIENT_ID = (string)$_POST['CLIENT_ID'];
                if (isset($_SESSION['TRUSTEDNET']['OAUTH'])) {
                    unset($_SESSION['TRUSTEDNET']['OAUTH']);
                }
            }
        }
        if ($CLIENT_ID != '') {
            COption::SetOptionString($module_id, "CLIENT_ID", $CLIENT_ID);
        }

        if (isset($_POST['CLIENT_SECRET'])) {
            if ($_POST['CLIENT_SECRET'] != $CLIENT_SECRET) {
                $CLIENT_SECRET = (string)$_POST['CLIENT_SECRET'];
                if (isset($_SESSION['TRUSTEDNET']['OAUTH'])) {
                    unset($_SESSION['TRUSTEDNET']['OAUTH']);
                }
            }
        }
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

<?
if (COption::GetOptionString("main", "new_user_email_uniq_check") === "Y") {
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
            <td width="60%"><input name="CLIENT_ID" style="width: 300px;" value="<?= $CLIENT_ID ?>"/></td>
        </tr>
        <tr>
            <td width="40%" class="adm-detail-content-cell-l"><?= GetMessage("TN_AUTH_CLIENT_SECRET") ?></td>
            <td width="60%"><input name="CLIENT_SECRET" value="<?= $CLIENT_SECRET ?>" type="password"/></td>
        </tr>
        <tr>
            <td class="adm-detail-content-cell-l">
                <input type="checkbox" <? echo($REGISTER_ENABLED ? "checked='checked'" : "") ?>
                       id="autoRegister"
                       name="REGISTER_ENABLED"
                       onchange="document.getElementById('templateId').disabled = !this.checked"/>
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
            <td class="adm-detail-content-cell-l">
                <?= GetMessage("TN_AUTH_USER_INFO_TEMPLATE_ID") ?>
                <span class="required"><sup>1</sup></span>
            </td>
            <td><input name="USER_INFO_TEMPLATE_ID"
                       id="templateId"
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

    <? $tabControl->BeginNextTab(); ?>
        <?
        $APPLICATION->IncludeComponent("trustednet:trustednet.auth", "");
        ?>
        <?
        $auth = OAuth2::getFromSession();
        if ($auth) {
        ?>
            <?
            $accessToken = $auth->getAccessToken();
            $apps = TAuthCommand::getAppList($accessToken);
            $appList = array();
            foreach ($apps as $app) {
                $appList[] = $app["clientId"];
            }
            if (in_array($CLIENT_ID, $appList)) {

                $yes = "<span style='color:green;'>" . GetMessage("TN_AUTH_YES") . "</span>";
                $no = "<span style='color:red;'>" . GetMessage("TN_AUTH_NO") . "</span>";
                $login = TAuthCommand::getAppParameters($accessToken, "login");
                $login = $login["data"];
                if ($login["limit"] == -1) {
                    $loginLimit = GetMessage("TN_AUTH_NO_LIMIT");
                } else {
                    $loginLimit = $login["limit"];
                }
            ?>
                <tr class="heading">
                    <td colspan="2"><?= GetMessage("TN_AUTH_INFO_LOGIN") ?></td>
                </tr>
                <tr>
                    <td width="50%"><?= GetMessage("TN_AUTH_INFO_LOGIN_AUTH") ?></td>
                    <td width="50%"><?= $login["isActive"] ? $yes : $no ?></td>
                </tr>
                <tr>
                    <td><?= GetMessage("TN_AUTH_INFO_ALLOW_OPEN_ACCESS") ?></td>
                    <td><?= $login["allowRegistration"] ? $yes : $no ?></td>
                </tr>
                <tr>
                    <td><?= GetMessage("TN_AUTH_INFO_ACTIVE_USERS") ?></td>
                    <td><?= $login["usersNumber"] ?></td>
                </tr>
                <tr>
                    <td><?= GetMessage("TN_AUTH_INFO_BLOCKED_USERS") ?></td>
                    <td><?= $login["blockedUsersNumber"] ?></td>
                </tr>
                <tr>
                    <td><?= GetMessage("TN_AUTH_INFO_USERS_SUMMARY") ?></td>
                    <td><?= (string)$login["usersNumber"] . " / " . (string)$loginLimit ?></td>
                </tr>

                <?
                $social = TAuthCommand::getAppParameters($accessToken, "social");
                $social = $social["data"];
                if ($social["limit"] == -1) {
                    $socialLimit = GetMessage("TN_AUTH_NO_LIMIT");
                } else {
                    $socialLimit = $social["limit"];
                }
                ?>
                <tr class="heading">
                    <td colspan="2"><?= GetMessage("TN_AUTH_INFO_SOCIAL") ?></td>
                </tr>
                <tr>
                    <td><?= GetMessage("TN_AUTH_INFO_SOCIAL_AUTH") ?></td>
                    <td><?= $social["isActive"] ? $yes : $no ?></td>
                </tr>
                <tr>
                    <td><?= GetMessage("TN_AUTH_INFO_ALLOW_OPEN_ACCESS") ?></td>
                    <td><?= $social["allowRegistration"] ? $yes : $no ?></td>
                </tr>
                <tr>
                    <td><?= GetMessage("TN_AUTH_INFO_SOCIAL_VK") ?></td>
                    <td><?= $social["vk"] ? $yes : $no ?></td>
                </tr>
                <tr>
                    <td><?= GetMessage("TN_AUTH_INFO_SOCIAL_FACEBOOK") ?></td>
                    <td><?= $social["fbook"] ? $yes : $no ?></td>
                </tr>
                <tr>
                    <td><?= GetMessage("TN_AUTH_INFO_SOCIAL_GOOGLE") ?></td>
                    <td><?= $social["google"] ? $yes : $no ?></td>
                </tr>
                <tr>
                    <td><?= GetMessage("TN_AUTH_INFO_SOCIAL_TWITTER") ?></td>
                    <td><?= $social["twitter"] ? $yes : $no ?></td>
                </tr>
                <tr>
                    <td><?= GetMessage("TN_AUTH_INFO_SOCIAL_MAILRU") ?></td>
                    <td><?= $social["mailru"] ? $yes : $no ?></td>
                </tr>
                <tr>
                    <td><?= GetMessage("TN_AUTH_INFO_ACTIVE_USERS") ?></td>
                    <td><?= $social["usersNumber"] ?></td>
                </tr>
                <tr>
                    <td><?= GetMessage("TN_AUTH_INFO_BLOCKED_USERS") ?></td>
                    <td><?= $social["blockedUsersNumber"] ?></td>
                </tr>
                <tr>
                    <td><?= GetMessage("TN_AUTH_INFO_USERS_SUMMARY") ?></td>
                    <td><?= (string)$social["usersNumber"] . " / " . (string)$socialLimit ?></td>
                </tr>

                <?
                $cert = TAuthCommand::getAppParameters($accessToken, "certificate");
                $cert = $cert["data"];
                if ($cert["limit"] == -1) {
                    $certLimit = GetMessage("TN_AUTH_NO_LIMIT");
                } else {
                    $certLimit = $cert["limit"];
                }
                ?>
                <tr class="heading">
                    <td colspan="2"><?= GetMessage("TN_AUTH_INFO_CERTIFICATE") ?></td>
                </tr>
                <tr>
                    <td><?= GetMessage("TN_AUTH_INFO_CERTIFICATE_AUTH") ?></td>
                    <td><?= $cert["isActive"] ? $yes : $no ?></td>
                </tr>
                <tr>
                    <td><?= GetMessage("TN_AUTH_INFO_ALLOW_OPEN_ACCESS") ?></td>
                    <td><?= $cert["allowRegistration"] ? $yes : $no ?></td>
                </tr>
                <tr>
                    <td><?= GetMessage("TN_AUTH_INFO_CERT_VALID_CHECK") ?></td>
                    <td><?= $cert["isValid"] ? $yes : $no ?></td>
                </tr>
                <tr>
                    <td><?= GetMessage("TN_AUTH_INFO_CERT_QUALITY_CHECK") ?></td>
                    <td><?= $cert["isQuality"] ? $yes : $no ?></td>
                </tr>
                <tr>
                    <td><?= GetMessage("TN_AUTH_INFO_ACTIVE_USERS") ?></td>
                    <td><?= $cert["usersNumber"] ?></td>
                </tr>
                <tr>
                    <td><?= GetMessage("TN_AUTH_INFO_BLOCKED_USERS") ?></td>
                    <td><?= $cert["blockedUsersNumber"] ?></td>
                </tr>
                <tr>
                    <td><?= GetMessage("TN_AUTH_INFO_USERS_SUMMARY") ?></td>
                    <td><?= (string)$cert["usersNumber"] . " / " . (string)$certLimit ?></td>
                </tr>
            <?
            } else {
            ?>
                <div>
                    <?= CAdminMessage::ShowMessage(GetMessage("TN_AUTH_INFO_WRONG_USER")) ?>
                </div>
            <?
            }
            ?>
        <?
        } else {
        ?>
            <div>
                <?= CAdminMessage::ShowNote(GetMessage("TN_AUTH_INFO_AUTH_REQ")) ?>
            </div>
        <?
        }
        ?>

    <? $tabControl->Buttons(); ?>

        <input type="submit" name="Update" value="<?= GetMessage("TN_SETTINGS_SAVE") ?>"/>

    <?$tabControl->End();?>

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
?>

<?echo BeginNote();?>
<span class="required"><sup>1</sup></span><?echo GetMessage("TN_AUTH_USER_INFO_TEMPLATE_ID_NOTE")?><br>
<?echo EndNote();?>

