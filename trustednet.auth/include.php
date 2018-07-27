<?php

define("LOG_FILENAME", $_SERVER["DOCUMENT_ROOT"] . "/sales-log.txt");

require_once $_SERVER["DOCUMENT_ROOT"] . '/bitrix/modules/trustednet.auth/classes/config.php';
require_once $_SERVER["DOCUMENT_ROOT"] . '/bitrix/modules/trustednet.auth/classes/general/trusted_auth.php';
require_once $_SERVER["DOCUMENT_ROOT"] . '/bitrix/modules/trustednet.auth/classes/general/oauth2.php';

