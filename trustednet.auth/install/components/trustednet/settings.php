<?php

// error_reporting(E_ALL);

define("TRUSTED_DEBUG", false);

define("TRUSTED_SSL_VERSION", 0);

/* ===== Database ===== */
// Использовать базу данных
define("TRUSTED_DB", true);


/* ===== Module trustednet ===== */
// Путь к модулю trustednet
define('TRUSTED_MODULE_PATH', '/bitrix/components/trustednet');


/* ===== Login ===== */
// Учетные данные приложения trusted.login
define("TRUSTED_LOGIN_CLIENT_ID", COption::GetOptionString("trustednet.auth", "CLIENT_ID", ""));
define("TRUSTED_LOGIN_CLIENT_SECRET", COption::GetOptionString("trustednet.auth", "CLIENT_SECRET", ""));
// Путь перехода после успешной аутентификации
define("TRUSTED_AUTHORIZED_REDIRECT", "../../index.php");
