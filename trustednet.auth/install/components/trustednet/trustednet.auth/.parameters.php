<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?><?

$arComponentParameters = array(
  "GROUPS" => array(
		"SOCIAL_LIST" => array(
			"NAME" => "Социальные сети",
		),
	),
	"PARAMETERS" => array(
    "TRUSTEDNET" => array(
      "PARENT" => "SOCIAL_LIST",
			"NAME" => "TrustedNet", 
			"TYPE" => "CHECKBOX",
      "DEFAULT" => "Y",
		),
    "VK" => array(
      "PARENT" => "SOCIAL_LIST",
			"NAME" => "VK", 
			"TYPE" => "CHECKBOX",
      "DEFAULT" => "Y",
		),
    "FACEBOOK" => array(
      "PARENT" => "SOCIAL_LIST",
			"NAME" => "FaceBook", 
			"TYPE" => "CHECKBOX",
      "DEFAULT" => "Y",
		),
    "GOOGLE" => array(
      "PARENT" => "SOCIAL_LIST",
			"NAME" => "Google+", 
			"TYPE" => "CHECKBOX",
      "DEFAULT" => "Y",
		),
    "MAIL" => array(
      "PARENT" => "SOCIAL_LIST",
			"NAME" => "Mail.ru", 
			"TYPE" => "CHECKBOX",
      "DEFAULT" => "Y",
		)
	),
);
?>
