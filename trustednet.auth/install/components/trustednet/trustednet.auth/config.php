<?php

define("TRUSTED_DEBUG", false);

define("TRUSTED_SSL_VERSION", 0);

define('TRUSTED_MODULE_PATH', '/bitrix/components/trustednet');

define('TRUSTED_PROJECT_ROOT', $_SERVER['DOCUMENT_ROOT']);
define('TRUSTED_MODULE_ROOT', TRUSTED_PROJECT_ROOT . TRUSTED_MODULE_PATH);

//Path login
define('TRUSTED_MODULE_AUTH_PATH', '/trustednet.auth');
define('TRUSTED_MODULE_AUTH_ROOT', TRUSTED_MODULE_ROOT . TRUSTED_MODULE_AUTH_PATH);
define('TRUSTED_MODULE_AUTH', TRUSTED_MODULE_AUTH_ROOT . '/common.php');

//Path sign
// TODO: Check trusted.sign exists
//define('TRUSTED_MODULE_SIGN_PATH', '/trustednet.sign');
//define('TRUSTED_MODULE_SIGN_ROOT', TRUSTED_MODULE_ROOT . TRUSTED_MODULE_SIGN_PATH);
//define('TRUSTED_MODULE_SIGN', TRUSTED_MODULE_SIGN_ROOT . '/common.php');

//Login
define("TRUSTED_LOGIN_CLIENT_ID", COption::GetOptionString("trustednet.auth", "CLIENT_ID", ""));
define("TRUSTED_LOGIN_CLIENT_SECRET", COption::GetOptionString("trustednet.auth", "CLIENT_SECRET", ""));
define("TRUSTED_AUTHORIZED_REDIRECT", "../../index.php");

//Database
define("TRUSTED_DB", true);
define("TRUSTEDNET_DB_TABLE_USER", "trn_user");

//TrustedNet URI
define('TRUSTED_COMMAND_URI_HOST', 'https://net.trusted.ru');
define('TRUSTED_COMMAND_REST', TRUSTED_COMMAND_URI_HOST . '/trustedapp/rest');
define('TRUSTED_COMMAND_APP', TRUSTED_COMMAND_URI_HOST . '/trustedapp/app');
define('TRUSTED_COMMAND_REST_APP_LIST', TRUSTED_COMMAND_REST . '/application/list');
define('TRUSTED_COMMAND_REST_LOGIN', TRUSTED_COMMAND_REST . '/application/auth/login');
define('TRUSTED_COMMAND_REST_SOCIAL', TRUSTED_COMMAND_REST . '/application/auth/social');
define('TRUSTED_COMMAND_REST_CERTIFICATE', TRUSTED_COMMAND_REST . '/application/auth/certificate');
define('TRUSTED_COMMAND_URI_OAUTH', TRUSTED_COMMAND_URI_HOST . '/idp/sso/oauth');
define('TRUSTED_COMMAND_URI_TOKEN', TRUSTED_COMMAND_URI_OAUTH . "/token");
define('TRUSTED_COMMAND_URI_CHECK_TOKEN', TRUSTED_COMMAND_URI_OAUTH . "/check_token");
define('TRUSTED_COMMAND_URI_LOGOUT', TRUSTED_COMMAND_URI_OAUTH . '/authorize/logout');
define('TRUSTED_COMMAND_URI_USERPROFILE', TRUSTED_COMMAND_URI_HOST . '/trustedapp/rest/user/profile/get');

//Module URI
define('TRUSTED_URI_HOST', 'https://' . $_SERVER["HTTP_HOST"]);
define('TRUSTED_URI_MODULE', TRUSTED_URI_HOST . TRUSTED_MODULE_PATH);
define('TRUSTED_URI_MODULE_AUTH', TRUSTED_URI_MODULE . TRUSTED_MODULE_AUTH_PATH);

// Upload
define('TRUSTED_UPLOAD_PATH', '/upload');
define('TRUSTED_UPLOAD_ROOT', TRUSTED_PROJECT_ROOT . TRUSTED_UPLOAD_PATH);
define('TRUSTED_UPLOAD_URI', TRUSTED_URI_HOST . TRUSTED_UPLOAD_PATH);

//OAuth params
define("TRUSTED_AUTH_REDIRECT_URI", TRUSTED_URI_MODULE_AUTH . "/authorize.php");
define("TRUSTED_AUTH_WIDGET_REDIRECT_URI", TRUSTED_URI_MODULE_AUTH . "/wauth.php");

//Token status
define("TRUSTEDNET_AUTH_TOKEN_STATUS_ERROR", 0);
define("TRUSTEDNET_AUTH_TOKEN_STATUS_NOT_EXPIRED", 1);
define("TRUSTEDNET_AUTH_TOKEN_STATUS_EXPIRED", 2);

//========== Errors ==========
//messages
define("TRUSTEDNET_ERROR_MSG_TOKEN_NOT_FOUND", "Token is not found");
define("TRUSTEDNET_ERROR_MSG_DIFFERENT_USER_ID", "Id of ServiceUser and TUser is different");
define("TRUSTEDNET_ERROR_MSG_CURL", "Wrong CURL request");
define("TRUSTEDNET_ERROR_MSG_ACCOUNT_NO_EMAIL", "User has not got email");
define("TRUSTEDNET_ERROR_MSG_ACCOUNT_CREATE", "Error on User account create");
define("TRUSTEDNET_ERROR_MSG_ACCOUNT_HAS_EMAIL", "User account has such email");
//codes
define("TRUSTEDNET_ERROR_CODE_TOKEN_NOT_FOUND", 1);
define("TRUSTEDNET_ERROR_CODE_DIFFERENT_USER_ID", 2);
define("TRUSTEDNET_ERROR_CODE_CURL", 3);
define("TRUSTEDNET_ERROR_CODE_ACCOUNT_NO_EMAIL", 4);
define("TRUSTEDNET_ERROR_CODE_ACCOUNT_CREATE", 5);
define("TRUSTEDNET_ERROR_CODE_ACCOUNT_HAS_EMAIL", 6);

