<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/trusted.id/config.php';

foreach (glob(TR_ID_MODULE_PATH . "/classes/*.php") as $filename) {
    require_once $filename;
}

