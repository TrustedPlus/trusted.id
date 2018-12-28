<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
    die();

use Trusted\Id;
use Bitrix\Main\Loader;

Loader::includeModule('trusted.id');
?>

    <script src="https://<?= TR_ID_SERVICE_HOST ?>/static/js/tlogin-3.0.1.js"></script>

<?
$token = Id\OAuth2::getFromSession();
if (!($USER && $USER->IsAuthorized())) {
    // Remove accesss token from session
    Id\OAuth2::remove();
    $token = NULL;
}
$defaultIn = $arParams['LOG_IN'] ? $arParams['LOG_IN'] : GetMessage('LOG_IN');
$defaultOut = $arParams['LOG_OUT'] ? $arParams['LOG_OUT'] : GetMessage('LOG_OUT');
if ($token) {
    echo '<div class="trn-profile">';
    echo '<img class="trn-profile_icon" src="' . $token->getUser()->getServiceUser()->getAvatarUrl($token->getAccessToken()) . '"/>';
    echo '<div class="trn-profile_name">' . $token->getUser()->getServiceUser()->getDisplayName() . '</div>';
    echo '<a class="trn-profile_exit" href="/?logout=yes">' . $defaultOut . '</a>';
    echo '</div>';
} else {
    ?>
    <div class='trusted-btn'
         onClick='TrustedID.login("<?= TR_ID_OPT_CLIENT_ID ?>", "<?= TR_ID_URI_HOST ?>/bitrix/components/trusted/id/authorize.php")'><?= $defaultIn ?></div>
    <?
}
