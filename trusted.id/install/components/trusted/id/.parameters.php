<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
    die();

$arComponentParameters = array(
    'GROUPS' => array(
        'BUTTON_NAME' => array(
            'NAME' => GetMessage('BUTTON_NAME'),
        ),
        'SOCIAL_LIST' => array(
            'NAME' => GetMessage('SOCIAL_LIST'),
        ),
    ),
    'PARAMETERS' => array(
        'LOG_IN' => array(
            'PARENT' => 'BUTTON_NAME',
            'NAME' => GetMessage('BUTTON_LOG_IN'),
            'TYPE' => 'String',
            'DEFAULT' => GetMessage('BUTTON_LOG_IN_DEFAULT'),
        ),
        'LOG_OUT' => array(
            'PARENT' => 'BUTTON_NAME',
            'NAME' => GetMessage('BUTTON_LOG_OUT'),
            'TYPE' => 'String',
            'DEFAULT' => GetMessage('BUTTON_LOG_OUT_DEFAULT'),
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

