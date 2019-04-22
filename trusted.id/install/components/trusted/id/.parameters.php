<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
    die();

$arComponentParameters = array(
    'GROUPS' => array(
        'LOOK' => array(
            'NAME' => GetMessage('TR_ID_COMP_LOOK'),
        ),
        'SOCIAL_LIST' => array(
            'NAME' => GetMessage('TR_ID_COMP_SOCIAL_LIST'),
        ),
    ),
    'PARAMETERS' => array(
        'LOG_IN' => array(
            'PARENT' => 'LOOK',
            'NAME' => GetMessage('TR_ID_COMP_BUTTON_LOG_IN'),
            'TYPE' => 'String',
            'DEFAULT' => GetMessage('TR_ID_COMP_BUTTON_LOG_IN_DEFAULT'),
        ),
        'LOG_OUT' => array(
            'PARENT' => 'LOOK',
            'NAME' => GetMessage('TR_ID_COMP_BUTTON_LOG_OUT'),
            'TYPE' => 'String',
            'DEFAULT' => GetMessage('TR_ID_COMP_BUTTON_LOG_OUT_DEFAULT'),
        ),
        'PERSONAL_LINK_ENABLE' => array(
            'PARENT' => 'LOOK',
            'NAME' => GetMessage('TR_ID_COMP_PERSONAL_LINK_ENABLE'),
            'TYPE' => 'CHECKBOX',
            'DEFAULT' => 'Y',
        ),
        'PERSONAL_LINK_URL' => array(
            'PARENT' => 'LOOK',
            'NAME' => GetMessage('TR_ID_COMP_PERSONAL_LINK_URL'),
            'TYPE' => 'String',
            'DEFAULT' => '/personal/',
        ),
        'PERSONAL_LINK_TEXT' => array(
            'PARENT' => 'LOOK',
            'NAME' => GetMessage('TR_ID_COMP_PERSONAL_LINK_TEXT'),
            'TYPE' => 'String',
            'DEFAULT' => GetMessage('TR_ID_COMP_PERSONAL_LINK_TEXT_DEFAULT'),
        ),
        'TRUSTED' => array(
            'PARENT' => 'SOCIAL_LIST',
            'NAME' => 'Id.Trusted.Plus',
            'TYPE' => 'CHECKBOX',
            'DEFAULT' => 'Y',
        ),
        'VK' => array(
            'PARENT' => 'SOCIAL_LIST',
            'NAME' => 'VK',
            'TYPE' => 'CHECKBOX',
            'DEFAULT' => 'Y',
        ),
        'FACEBOOK' => array(
            'PARENT' => 'SOCIAL_LIST',
            'NAME' => 'FaceBook',
            'TYPE' => 'CHECKBOX',
            'DEFAULT' => 'Y',
        ),
        'GOOGLE' => array(
            'PARENT' => 'SOCIAL_LIST',
            'NAME' => 'Google+',
            'TYPE' => 'CHECKBOX',
            'DEFAULT' => 'Y',
        ),
        'MAIL' => array(
            'PARENT' => 'SOCIAL_LIST',
            'NAME' => 'Mail.ru',
            'TYPE' => 'CHECKBOX',
            'DEFAULT' => 'Y',
        )
    ),
);

