<?php

use Trusted\Id;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;

Loader::includeModule('trusted.id');
$module_id = TR_ID_MODULE_ID;
IncludeModuleLangFile(__FILE__);

$aTabs = array(
    array(
        'DIV' => 'tab_settings',
        'TAB' => GetMessage('TR_ID_OPTIONS_TAB_SETTINGS'),
        'TITLE' => GetMessage('TR_ID_OPTIONS_TAB_SETTINGS_TITLE')
    ),
    array(
        'DIV' => 'tab_info',
        'TAB' => GetMessage('TR_ID_OPTIONS_TAB_INFO'),
        'TITLE' => GetMessage('TR_ID_OPTIONS_TAB_INFO_TITLE')
    ),
);

$tabControl = new CAdminTabControl('trustedTabControl', $aTabs, true, true);

$CLIENT_ID = Option::get($module_id, 'CLIENT_ID', '');
$CLIENT_SECRET = Option::get($module_id, 'CLIENT_SECRET', '');
$REGISTER_ENABLED = Option::get($module_id, 'REGISTER_ENABLED', '');
$USER_INFO_TEMPLATE_ID = Option::get($module_id, 'USER_INFO_TEMPLATE_ID', '2');
$SEND_MAIL_ENABLED = TR_ID_DEFAULT_SHOULD_SEND_MAIL;
$REDIRECT_URL = Option::get($module_id, 'REDIRECT_URL', '');

function CheckRedirectUrl($url)
{
    $res = true;
    if ($url == '')
        return true;
    if (!filter_var($url, FILTER_VALIDATE_URL))
        $res = false;
    if (!preg_match('/^(http:\/\/|https:\/\/).*/', $url))
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
            Option::set($module_id, 'CLIENT_ID', $CLIENT_ID);
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
            Option::set($module_id, 'CLIENT_SECRET', $CLIENT_SECRET);
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
                Option::set($module_id, 'REDIRECT_URL', $REDIRECT_URL_POST);
            } else {
                CAdminMessage::ShowMessage(GetMessage('TR_ID_REDIRECT_URL_INVALID'));
            }
        }

        Option::set($module_id, 'REGISTER_ENABLED', $REGISTER_ENABLED);
        Option::set($module_id, 'USER_INFO_TEMPLATE_ID', $USER_INFO_TEMPLATE_ID);
        Option::set($module_id, 'SEND_MAIL_ENABLED', $SEND_MAIL_ENABLED);
    }
}

if (!Id\Utils::isSecure()) {
    echo BeginNote(), GetMessage('TR_ID_HTTP_WARNING'), EndNote();
}

if (Option::get('main', 'new_user_email_uniq_check') !== 'Y') {
?>
    <h3 style="margin-bottom: 10px;">
        <?= GetMessage('TR_ID_SET_EMAIL_UNIQ_CHECK_PREFIX') ?>
        </br>"<i><?= GetMessage('TR_ID_REGISTER_EMAIL_UNIQ_CHECK_RU') ?></i>"</br>
        <a href="/bitrix/admin/settings.php?lang=ru&mid=main&tabControl_active_tab=edit6#opt_new_user_registration_email_confirmation">
            <?= GetMessage('TR_ID_SET_EMAIL_UNIQ_CHECK_POSTFIX') ?>
        </a>
    </h3>
<?

} elseif (!Id\Utils::checkCurl()) {
?>
    <h3>
        <? echo BeginNote(), GetMessage('TR_ID_CURL_WARNING'), EndNote();?>
    </h3>
<?
} else {
    $tabControl->Begin();
    $tabControl->BeginNextTab();
?>

    <div style="margin-bottom: 40px; position:relative">
        <h3>
            <?= GetMessage('TR_ID_EMAIL_UNIQ_CHECK_TITLE') ?>!
        </h3>
        <div>
            <?= GetMessage('TR_ID_EMAIL_UNIQ_CHECK_PREFIX') ?>
            "<i><b><?= GetMessage('TR_ID_REGISTER_EMAIL_UNIQ_CHECK_RU') ?></b></i>"
            <?= GetMessage('TR_ID_EMAIL_UNIQ_CHECK_POSTFIX') ?>
        </div>
        <div style="position:absolute; right: 10px; top: -10px">
            <a href="https://id.trusted.plus/" target="_blank"><?= GetMessage('TR_ID_SERVICE_LINK') ?></a>
        </div>
    </div>

    <form method="POST" action="<? echo $APPLICATION->GetCurPage() ?>?lang=<? echo LANGUAGE_ID ?>&mid=<?= $module_id ?>"
          name="currency_settings">
        <? echo bitrix_sessid_post(); ?>
        <tr>
            <td width="40%" class="adm-detail-content-cell-l"><?= GetMessage('TR_ID_CLIENT_ID') ?></td>
            <td width="60%"><input id="CLIENT_ID" name="CLIENT_ID" style="width: 300px;" value="<?= $CLIENT_ID ?>" type="text"/></td>
        </tr>
        <tr>
            <td width="40%" class="adm-detail-content-cell-l"><?= GetMessage('TR_ID_CLIENT_SECRET') ?></td>
            <td width="60%"><input id="CLIENT_SECRET" name="CLIENT_SECRET" value="<?= $CLIENT_SECRET ?>" type="password"/></td>
        </tr>
        <tr>
            <td class="adm-detail-content-cell-l">
                <input type="checkbox" <? echo($REGISTER_ENABLED ? "checked='checked'" : "") ?>
                       id="autoRegister"
                       name="REGISTER_ENABLED"
                       onchange="document.getElementById('templateId').disabled = !this.checked"/>
            </td>
            <td>
                <label for="autoRegister"><?= GetMessage('TR_ID_ENABLE_AUTO_REGISTRATION') ?></label>
            </td>
        </tr>
        <?php
        if (TR_ID_USE_SEND_MAIL_SETTINGS) {
        ?>
            <tr>
                <td class="adm-detail-content-cell-l">
                    <input type="checkbox"
                        <?= (($SEND_MAIL_ENABLED) ? "checked='checked'" : "") ?>
                        <?= $REGISTER_ENABLED ? "" : "disabled='disabled'" ?>
                           name="SEND_MAIL_ENABLED"/>
                </td>
                <td>
                    <label for="autoRegister"><?= GetMessage('TR_ID_ENABLE_SEND_MAIL') ?></label>
                </td>
            </tr>
        <?
        }
        ?>
        <tr>
            <td class="adm-detail-content-cell-l">
                <?= GetMessage('TR_ID_USER_INFO_TEMPLATE_ID') ?>
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
            <td colspan="2">
                <?echo BeginNote();?>
                <?echo GetMessage('TR_ID_USER_INFO_TEMPLATE_ID_NOTE')?><br>
                <?echo EndNote();?>
            </td>
        </tr>
        <tr>
            <td width="40%" class="adm-detail-content-cell-l"><?= GetMessage('TR_ID_REDIRECT_URL') ?></td>
            <td width="60%">
                <input name="REDIRECT_URL" style="width: 400px;" value="<?= $REDIRECT_URL ?>" type="text"/>
            </td>
        </tr>

    <? $tabControl->BeginNextTab(); ?>
        <?
        $APPLICATION->IncludeComponent('trusted:id', '');
        ?>
        <?
        $auth = Id\OAuth2::getFromSession();
        if ($auth) {
        ?>
            <?
            $accessToken = $auth->getAccessToken();
            $apps = Id\TAuthCommand::getAppList($accessToken);
            $appList = array();
            foreach ($apps as $app) {
                $appList[] = $app['clientId'];
            }
            if (in_array($CLIENT_ID, $appList)) {

                $yes = "<span style='color:green;'>" . GetMessage('TR_ID_YES') . '</span>';
                $no = "<span style='color:red;'>" . GetMessage('TR_ID_NO') . '</span>';

                $login = Id\TAuthCommand::getAppParameters($accessToken, 'login');
                $login = $login['data'];
                if ($login['limit'] == -1) {
                    $loginLimit = GetMessage('TR_ID_NO_LIMIT');
                } else {
                    $loginLimit = $login['limit'];
                }

                $social = Id\TAuthCommand::getAppParameters($accessToken, 'social');
                $social = $social['data'];
                if ($social['limit'] == -1) {
                    $socialLimit = GetMessage('TR_ID_NO_LIMIT');
                } else {
                    $socialLimit = $social['limit'];
                }

                $cert = Id\TAuthCommand::getAppParameters($accessToken, 'certificate');
                $cert = $cert['data'];
                if ($cert['limit'] == -1) {
                    $certLimit = GetMessage('TR_ID_NO_LIMIT');
                } else {
                    $certLimit = $cert['limit'];
                }
                $tariffEnd = gmdate('d.m.Y', substr($cert['tariffEnd'], 0, -3));
            ?>
                <tr class="heading">
                    <td colspan="2"><?= GetMessage('TR_ID_INFO_TARIFF') ?></td>
                </tr>
                <tr>
                    <td width="50%"><?= GetMessage('TR_ID_INFO_TARIFF_NAME') ?></td>
                    <td width="50%"><?= $cert['tariff']['name'] ?></td>
                </tr>
                <tr>
                    <td width="50%"><?= GetMessage('TR_ID_INFO_TARIFF_ACTIVE') ?></td>
                    <td width="50%"><?= $cert['tariff']['enabled'] ? $yes : $no ?></td>
                </tr>
                <tr>
                    <td width="50%"><?= GetMessage('TR_ID_INFO_TARIFF_END') ?></td>
                    <td width="50%"><?= $tariffEnd ?></td>
                </tr>

                <tr class="heading">
                    <td colspan="2"><?= GetMessage('TR_ID_INFO_LOGIN') ?></td>
                </tr>
                <tr>
                    <td width="50%"><?= GetMessage('TR_ID_INFO_LOGIN_AUTH') ?></td>
                    <td width="50%"><?= $login['isActive'] ? $yes : $no ?></td>
                </tr>
                <tr>
                    <td><?= nl2br(GetMessage('TR_ID_INFO_ALLOW_OPEN_ACCESS')) ?></td>
                    <td><?= $login['allowRegistration'] ? $yes : $no ?></td>
                </tr>
                <tr>
                    <td><?= GetMessage('TR_ID_INFO_ACTIVE_USERS') ?></td>
                    <td><?= $login['usersNumber'] ?></td>
                </tr>
                <tr>
                    <td><?= GetMessage('TR_ID_INFO_BLOCKED_USERS') ?></td>
                    <td><?= $login['blockedUsersNumber'] ?></td>
                </tr>
                <tr>
                    <td><?= GetMessage('TR_ID_INFO_USERS_SUMMARY') ?></td>
                    <td><?= (string)$login['usersNumber'] . ' / ' . (string)$loginLimit ?></td>
                </tr>

                <tr class="heading">
                    <td colspan="2"><?= GetMessage('TR_ID_INFO_SOCIAL') ?></td>
                </tr>
                <tr>
                    <td><?= GetMessage('TR_ID_INFO_SOCIAL_AUTH') ?></td>
                    <td><?= $social['isActive'] ? $yes : $no ?></td>
                </tr>
                <tr>
                    <td><?= nl2br(GetMessage('TR_ID_INFO_ALLOW_OPEN_ACCESS')) ?></td>
                    <td><?= $social['allowRegistration'] ? $yes : $no ?></td>
                </tr>
                <tr>
                    <td><?= GetMessage('TR_ID_INFO_SOCIAL_VK') ?></td>
                    <td><?= $social['vk'] ? $yes : $no ?></td>
                </tr>
                <tr>
                    <td><?= GetMessage('TR_ID_INFO_SOCIAL_FACEBOOK') ?></td>
                    <td><?= $social['fbook'] ? $yes : $no ?></td>
                </tr>
                <tr>
                    <td><?= GetMessage('TR_ID_INFO_SOCIAL_GOOGLE') ?></td>
                    <td><?= $social['google'] ? $yes : $no ?></td>
                </tr>
                <tr>
                    <td><?= GetMessage('TR_ID_INFO_SOCIAL_TWITTER') ?></td>
                    <td><?= $social['twitter'] ? $yes : $no ?></td>
                </tr>
                <tr>
                    <td><?= GetMessage('TR_ID_INFO_SOCIAL_MAILRU') ?></td>
                    <td><?= $social['mailru'] ? $yes : $no ?></td>
                </tr>
                <tr>
                    <td><?= GetMessage('TR_ID_INFO_ACTIVE_USERS') ?></td>
                    <td><?= $social['usersNumber'] ?></td>
                </tr>
                <tr>
                    <td><?= GetMessage('TR_ID_INFO_BLOCKED_USERS') ?></td>
                    <td><?= $social['blockedUsersNumber'] ?></td>
                </tr>
                <tr>
                    <td><?= GetMessage('TR_ID_INFO_USERS_SUMMARY') ?></td>
                    <td><?= (string)$social['usersNumber'] . ' / ' . (string)$socialLimit ?></td>
                </tr>

                <tr class="heading">
                    <td colspan="2"><?= GetMessage('TR_ID_INFO_CERTIFICATE') ?></td>
                </tr>
                <tr>
                    <td><?= GetMessage('TR_ID_INFO_CERTIFICATE_AUTH') ?></td>
                    <td><?= $cert['isActive'] ? $yes : $no ?></td>
                </tr>
                <tr>
                    <td><?= nl2br(GetMessage('TR_ID_INFO_ALLOW_OPEN_ACCESS')) ?></td>
                    <td><?= $cert['allowRegistration'] ? $yes : $no ?></td>
                </tr>
                <tr>
                    <td><?= GetMessage('TR_ID_INFO_CERT_VALID_CHECK') ?></td>
                    <td><?= $cert['isValid'] ? $yes : $no ?></td>
                </tr>
                <tr>
                    <td><?= GetMessage('TR_ID_INFO_CERT_QUALITY_CHECK') ?></td>
                    <td><?= $cert['isQuality'] ? $yes : $no ?></td>
                </tr>
                <tr>
                    <td><?= GetMessage('TR_ID_INFO_ACTIVE_USERS') ?></td>
                    <td><?= $cert['usersNumber'] ?></td>
                </tr>
                <tr>
                    <td><?= GetMessage('TR_ID_INFO_BLOCKED_USERS') ?></td>
                    <td><?= $cert['blockedUsersNumber'] ?></td>
                </tr>
                <tr>
                    <td><?= GetMessage('TR_ID_INFO_USERS_SUMMARY') ?></td>
                    <td><?= (string)$cert['usersNumber'] . ' / ' . (string)$certLimit ?></td>
                </tr>
            <?
            } else {
            ?>
                <div>
                    <?= CAdminMessage::ShowMessage(GetMessage('TR_ID_INFO_WRONG_USER')) ?>
                </div>
            <?
            }
            ?>
        <?
        } else {
        ?>
            <div>
                <?= CAdminMessage::ShowNote(GetMessage('TR_ID_INFO_AUTH_REQ')) ?>
            </div>
        <?
        }
        ?>

    <? $tabControl->Buttons(); ?>

        <input type="submit" name="Update" value="<?= GetMessage('TR_ID_SETTINGS_SAVE') ?>"/>

    <?$tabControl->End();?>

    </form>

<?
}

