<?php

use Bitrix\Main\Config\Option;

if (!check_bitrix_sessid()) return;
echo CAdminMessage::ShowNote(GetMessage('MOD_INST_OK'));
if (Option::get('main', 'new_user_email_uniq_check') === 'N') {
    ?>
    <div style="margin-bottom: 10px;">
        <?= GetMessage('TR_ID_SET_EMAIL_UNIQ_CHECK_PREFIX') ?>
        "<?= GetMessage('TR_ID_REGISTER_EMAIL_UNIQ_CHECK') ?>"
        <a href="/bitrix/admin/settings.php?lang=ru&mid=main&tabControl_active_tab=edit6#opt_new_user_registration_email_confirmation">
            <?= GetMessage('TR_ID_SET_EMAIL_UNIQ_CHECK_POSTFIX') ?>
        </a>
    </div>
    <?
}
?>

<form action="<? echo $APPLICATION->GetCurPage() ?>">
    <input type="hidden" name="lang" value="<? echo LANG ?>">
    <input type="submit" name="" value="<? echo GetMessage('MOD_BACK') ?>">
    <form>

