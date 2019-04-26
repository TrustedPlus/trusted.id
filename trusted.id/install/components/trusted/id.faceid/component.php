<?php
if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)die();

use Bitrix\Main\Loader;
use Bitrix\Main\Config\Option;

$module_id = TR_ID_MODULE_ID;
Loader::includeModule($module_id);

$E_VISION_KEY = Option::get(TR_ID_MODULE_ID, 'E_VISION_KEY', '');
$arResult['KEY'] = $E_VISION_KEY != "" ? true : false;

$this->IncludeComponentTemplate();
