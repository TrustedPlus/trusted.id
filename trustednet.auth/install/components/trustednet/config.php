<?php

require_once __DIR__ . '/settings.php';

define('TRUSTED_PROJECT_ROOT', $_SERVER['DOCUMENT_ROOT']);
define('TRUSTED_MODULE_ROOT', TRUSTED_PROJECT_ROOT . TRUSTED_MODULE_PATH);

define('TRUSTED_URI_HOST', 'https://' . $_SERVER["HTTP_HOST"]);
define('TRUSTED_URI_MODULE', TRUSTED_URI_HOST . TRUSTED_MODULE_PATH);

define('TRUSTED_COMMAND_URI_HOST', 'https://net.trusted.ru');
define('TRUSTED_COMMAND_REST', TRUSTED_COMMAND_URI_HOST . '/trustedapp/rest');
define('TRUSTED_COMMAND_APP', TRUSTED_COMMAND_URI_HOST . '/trustedapp/app');

/* upload */
define('TRUSTED_UPLOAD_PATH', '/upload');
define('TRUSTED_UPLOAD_ROOT', TRUSTED_PROJECT_ROOT . TRUSTED_UPLOAD_PATH);
define('TRUSTED_UPLOAD_URI', TRUSTED_URI_HOST . TRUSTED_UPLOAD_PATH);

//path login
define('TRUSTED_MODULE_DATA_PATH', '/trustednet.data');
define('TRUSTED_MODULE_DATA_ROOT', TRUSTED_MODULE_ROOT . TRUSTED_MODULE_DATA_PATH);
define('TRUSTED_MODULE_DATA', TRUSTED_MODULE_DATA_ROOT . '/common.php');

//path login
define('TRUSTED_MODULE_AUTH_PATH', '/trustednet.auth');
define('TRUSTED_MODULE_AUTH_ROOT', TRUSTED_MODULE_ROOT . TRUSTED_MODULE_AUTH_PATH);
define('TRUSTED_MODULE_AUTH', TRUSTED_MODULE_AUTH_ROOT . '/common.php');

////path sign
// TOSO: Check trusted.sign exists
//define('TRUSTED_MODULE_SIGN_PATH', '/trustednet.sign');
//define('TRUSTED_MODULE_SIGN_ROOT', TRUSTED_MODULE_ROOT . TRUSTED_MODULE_SIGN_PATH);
//define('TRUSTED_MODULE_SIGN', TRUSTED_MODULE_SIGN_ROOT . '/common.php');

require_once(TRUSTED_MODULE_ROOT . '/util.php');


