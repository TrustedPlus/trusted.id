<?php
use Bitrix\Main\Localization\Loc;

$arComponentParameters = array(
    'GROUPS' => array(
        'SETTINGS' => array(
            'NAME' => Loc::getMessage("TR_ID_COMP_SETTINGS"),
        ),
    ),
    'PARAMETERS' => array(
        'TIME_STEP' => array(
            'PARENT' => 'SETTINGS',
            'NAME' => Loc::getMessage("TR_ID_COMP_TIME_STEP"),
            'TYPE' => 'STRING',
            'DEFAULT' => '5',
        ),
    )
);