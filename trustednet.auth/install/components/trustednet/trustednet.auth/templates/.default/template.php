<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
    die();
LOC
?>

<?php
$SERVICE_HOST = COption::GetOptionString(TN_AUTH_MODULE_ID, "SERVICE_HOST", "");
?>
<link rel="stylesheet" href="https://<?= $SERVICE_HOST ?>/static/css/tlogin-3.0.1.css">
<script src="https://<?= $SERVICE_HOST ?>/static/js/tlogin-3.0.1.js"></script>


<!--<link rel="stylesheet" type="text/css" href="https://net.trusted.ru/static/css/tlogin.css">-->
<?php
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/trustednet.auth/classes/config.php");
require_once(TRUSTED_MODULE_PATH . "/classes/general/oauth2.php");
$token = OAuth2::getFromSession();
if (!($USER && $USER->IsAuthorized())) {
    // Remove accesss token from session
    OAuth2::remove();
    $token = NULL;
}
if ($token) {
    echo '<div class="trn-profile">';
    echo '<img class="trn-profile_icon" src="' . $token->getUser()->getServiceUser()->getAvatarUrl($token->getAccessToken()) . '"/>';
    echo '<div class="trn-profile_name">' . $token->getUser()->getServiceUser()->getDisplayName() . '</div>';
    echo '<a class="trn-profile_exit" href="/?logout=yes">' . GetMessage("AUTH_LOGOUT") . '</a>';
    echo '</div>';
} else {
    ?>
    <div class='trusted-btn' onClick='TrustedNet.login("<?= TRUSTED_LOGIN_CLIENT_ID ?>", "<?= TRUSTED_URI_HOST ?>/bitrix/components/trustednet/trustednet.auth/authorize.php")'> <?= GetMessage("AUTH") ?></div>

<?php }

