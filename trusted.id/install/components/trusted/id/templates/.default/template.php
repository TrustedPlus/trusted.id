<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
    die();

use Trusted\Id;
use Bitrix\Main\Loader;

Loader::includeModule('trusted.id');
?>

<script src="<?= TR_ID_SCRIPT_JS ?>"></script>

<?
$token = Id\OAuth2::getFromSession();
if (!($USER && $USER->IsAuthorized())) {
    // Remove accesss token from session
    Id\OAuth2::remove();
    $token = NULL;
}
$defaultIn = $arParams['LOG_IN'] ?: GetMessage('LOG_IN');
$defaultOut = $arParams['LOG_OUT'] ?: GetMessage('LOG_OUT');
$personalLinkEnable = $arParams['PERSONAL_LINK_ENABLE'] == 'Y';
$personalLinkUrl = $arParams['PERSONAL_LINK_URL'] ?: '/personal/';
$personalLinkText = $arParams['PERSONAL_LINK_TEXT'] ?: GetMessage('PERSONAL_LINK_TEXT');

$version = TR_ID_OPT_SERVICE_VERSION;
$requestType = strcmp($version, "1.3") == 0 ? 'GET' : 'POST';

if ($token) {
    ?>
    <div class="trn-profile">
        <div class="trn-text-wrap">
            <div class="trn-profile_name"><?= $token->getUser()->getServiceUser()->getDisplayName() ?></div>
            <?
            if ($personalLinkEnable) {
            ?>
                <div class="trn-profile_name">
                    <a href="<?= $personalLinkUrl ?>">
                        <?= $personalLinkText ?>
                    </a>
                </div>
            <?
            }
            ?>
            <a class="trn-profile_exit" href="/?logout=yes"><?= $defaultOut ?></a>
        </div>
        <img class="trn-profile_icon" src="<?=$token->getUser()->getServiceUser()->getAvatarUrl($token->getAccessToken()) ?>"/>
    </div>
    <?
} else {
    ?>
    <?
    if(strcmp($version, "1.3") == 0) {
    ?>
    <div class='trusted-btn'
         onClick='TrustedID.login("<?= TR_ID_OPT_CLIENT_ID ?>", "<?= TR_ID_URI_HOST ?>/bitrix/components/trusted/id/authorize.php")'><?= $defaultIn ?></div>
         <?
            } else { ?>
    <div class='trusted-btn'
         onClick='TrustedID.login("<?= TR_ID_COMMAND_URI_HOST ?>", "<?= TR_ID_OPT_CLIENT_ID ?>", "<?= TR_ID_URI_HOST ?>/bitrix/components/trusted/id/authorize.php")'><?= $defaultIn ?></div>
         <?
         } 
        ?>
    <?
}
