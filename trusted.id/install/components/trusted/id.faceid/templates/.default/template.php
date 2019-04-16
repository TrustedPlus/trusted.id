<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Trusted\Id;
use Bitrix\Main\Localization\Loc;use Trusted\Id\TDataBaseUser;

?>

<script src="/bitrix/js/trusted.id/tracking-min.js"></script>
<script src="/bitrix/js/trusted.id/face-min.js"></script>

<div class="demo-frame">
    <div id="userFIO"></div>
    <input type="hidden" id="userId">
    <div
    <div class="demo-container">
        <video  id="video" width="320" height="240" preload autoplay loop muted></video>
        <canvas id="canvas" width="320" height="240"></canvas>
    </div>
</div>

<script>
    let ajaxUrl = '<?= TR_ID_AJAX_CONTROLLER . '?command=find' ?>'
</script>
