<?php

use Bitrix\Main\Config\Option;

// TODO: clean up the constants

define('TR_ID_MODULE_ID', 'trusted.id');

define('TR_ID_USE_SEND_MAIL_SETTINGS', false);
define('TR_ID_DEFAULT_SHOULD_SEND_MAIL', false);
define('TR_ID_DEBUG', false);
define('TR_ID_LOG_FILENAME', $_SERVER['DOCUMENT_ROOT'] . '/trusted.id-log.txt');

define('TR_ID_SSL_VERSION', 0);

define('TR_ID_COMPONENT_PATH', '/bitrix/components/trusted');

define('TR_ID_PROJECT_ROOT', $_SERVER['DOCUMENT_ROOT']);
define('TR_ID_MODULE_ROOT', TR_ID_PROJECT_ROOT . TR_ID_COMPONENT_PATH);
define('TR_ID_MODULE_PATH', TR_ID_PROJECT_ROOT . '/bitrix/modules/trusted.id');

//Path login
define('TR_ID_MODULE_AUTH_PATH', '/trusted.id');
define('TR_ID_MODULE_AUTH_ROOT', TR_ID_MODULE_ROOT . TR_ID_MODULE_AUTH_PATH);
define('TR_ID_MODULE_AUTH', TR_ID_MODULE_AUTH_ROOT . '/common.php');

//Login
define('TR_ID_OPT_REGISTER_ENABLED', Option::get(TR_ID_MODULE_ID, 'REGISTER_ENABLED', ''));
define('TR_ID_OPT_CLIENT_ID', Option::get(TR_ID_MODULE_ID, 'CLIENT_ID', ''));
define('TR_ID_OPT_CLIENT_SECRET', Option::get(TR_ID_MODULE_ID, 'CLIENT_SECRET', ''));

//Database

define('TR_ID_DB', true);
define('TR_ID_DB_TABLE_USER', 'tr_id_users');

//Trusted URI
define('TR_ID_SERVICE_HOST', 'id.trusted.plus');
define('TR_ID_COMMAND_URI_HOST', 'https://' . TR_ID_SERVICE_HOST);
define('TR_ID_COMMAND_REST', TR_ID_COMMAND_URI_HOST . '/trustedapp/rest');
define('TR_ID_COMMAND_APP', TR_ID_COMMAND_URI_HOST . '/trustedapp/app');
define('TR_ID_COMMAND_REST_APP_LIST', TR_ID_COMMAND_REST . '/application/list');
define('TR_ID_COMMAND_REST_LOGIN', TR_ID_COMMAND_REST . '/application/auth/login');
define('TR_ID_COMMAND_REST_SOCIAL', TR_ID_COMMAND_REST . '/application/auth/social');
define('TR_ID_COMMAND_REST_CERTIFICATE', TR_ID_COMMAND_REST . '/application/auth/certificate');
define('TR_ID_COMMAND_URI_OAUTH', TR_ID_COMMAND_URI_HOST . '/idp/sso/oauth');
define('TR_ID_COMMAND_URI_TOKEN', TR_ID_COMMAND_URI_OAUTH . '/token');
define('TR_ID_COMMAND_URI_CHECK_TOKEN', TR_ID_COMMAND_URI_OAUTH . '/check_token');
define('TR_ID_COMMAND_URI_LOGOUT', TR_ID_COMMAND_URI_OAUTH . '/authorize/logout');
define('TR_ID_COMMAND_URI_USERPROFILE', TR_ID_COMMAND_URI_HOST . '/trustedapp/rest/user/profile/get');
define('TR_ID_COMMAND_AUTHORIZE_PROFILE', TR_ID_COMMAND_URI_HOST . '/idp/sso/user/authorize/profile');
define('TR_ID_COMMAND_AUTHORIZE_IDENTITY', TR_ID_COMMAND_URI_HOST . '/idp/sso/user/authorize/identity');
define('TR_ID_COMMAND_REVOKE_TOKEN', TR_ID_COMMAND_URI_HOST . '/idp/sso/oauth/revoke');

//Module URI
define('TR_ID_URI_HOST', 'https://' . preg_replace('/:\d+$/', '', $_SERVER['HTTP_HOST'])); // remove port from host
define('TR_ID_URI_MODULE', TR_ID_URI_HOST . TR_ID_COMPONENT_PATH);
define('TR_ID_URI_MODULE_AUTH', TR_ID_URI_MODULE . '/id');
define("TR_ID_AJAX_CONTROLLER", TR_ID_URI_MODULE . "/id.api/ajax.php");

// Upload
define('TR_ID_UPLOAD_PATH', '/upload');
define('TR_ID_UPLOAD_ROOT', TR_ID_PROJECT_ROOT . TR_ID_UPLOAD_PATH);
define('TR_ID_UPLOAD_URI', TR_ID_URI_HOST . TR_ID_UPLOAD_PATH);

//OAuth params
define('TR_ID_REDIRECT_URI', TR_ID_URI_MODULE_AUTH . '/authorize.php');

//Token status
define('TR_ID_TOKEN_STATUS_ERROR', 0);
define('TR_ID_TOKEN_STATUS_NOT_EXPIRED', 1);
define('TR_ID_TOKEN_STATUS_EXPIRED', 2);

//========== Errors ==========
//messages
define('TR_ID_ERROR_MSG_TOKEN_NOT_FOUND', 'Token is not found');
define('TR_ID_ERROR_MSG_DIFFERENT_USER_ID', 'Id of ServiceUser and TUser is different');
define('TR_ID_ERROR_MSG_CURL', 'Wrong CURL request');
define('TR_ID_ERROR_MSG_ACCOUNT_NO_EMAIL', 'User has not got email');
define('TR_ID_ERROR_MSG_ACCOUNT_CREATE', 'Error on User account create');
define('TR_ID_ERROR_MSG_ACCOUNT_HAS_EMAIL', 'User account has such email');
//codes
define('TR_ID_ERROR_CODE_TOKEN_NOT_FOUND', 1);
define('TR_ID_ERROR_CODE_DIFFERENT_USER_ID', 2);
define('TR_ID_ERROR_CODE_CURL', 3);
define('TR_ID_ERROR_CODE_ACCOUNT_NO_EMAIL', 4);
define('TR_ID_ERROR_CODE_ACCOUNT_CREATE', 5);
define('TR_ID_ERROR_CODE_ACCOUNT_HAS_EMAIL', 6);

