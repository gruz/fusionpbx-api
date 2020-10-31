<?php

	//application details
		$apps[$x]['name'] = "VTiger CRM connector";
		$apps[$x]['uuid'] = "48fa4c96-61c9-4eff-aa7f-cdfa77ce2e86";
		$apps[$x]['category'] = "Integration";
		$apps[$x]['subcategory'] = "";
		$apps[$x]['version'] = "1.0";
		$apps[$x]['license'] = "Mozilla Public License 1.1";
		$apps[$x]['url'] = "http://www.fusionpbx.com";
		$apps[$x]['description']['en-us'] = "";
		$apps[$x]['description']['ar-eg'] = "";
		$apps[$x]['description']['de-at'] = "";
		$apps[$x]['description']['de-ch'] = "";
		$apps[$x]['description']['de-de'] = "";
		$apps[$x]['description']['es-cl'] = "";
		$apps[$x]['description']['es-mx'] = "";
		$apps[$x]['description']['fr-ca'] = "";
		$apps[$x]['description']['fr-fr'] = "";
		$apps[$x]['description']['he-il'] = "";
		$apps[$x]['description']['it-it'] = "";
		$apps[$x]['description']['nl-nl'] = "";
		$apps[$x]['description']['pl-pl'] = "";
		$apps[$x]['description']['pt-br'] = "";
		$apps[$x]['description']['pt-pt'] = "";
		$apps[$x]['description']['ro-ro'] = "";
		$apps[$x]['description']['ru-ru'] = "";
		$apps[$x]['description']['sv-se'] = "";
		$apps[$x]['description']['uk-ua'] = "";

	//default settings
		$y=0;
		$apps[$x]['default_settings'][$y]['default_setting_uuid'] = "9896b9a4-6f1f-4020-923a-b91efe284732";
		$apps[$x]['default_settings'][$y]['default_setting_category'] = "vtiger_connector";
		$apps[$x]['default_settings'][$y]['default_setting_subcategory'] = "enable";
		$apps[$x]['default_settings'][$y]['default_setting_name'] = "boolean";
		$apps[$x]['default_settings'][$y]['default_setting_value'] = "false";
		$apps[$x]['default_settings'][$y]['default_setting_enabled'] = "false";
		$apps[$x]['default_settings'][$y]['default_setting_description'] = "Enable VTiger CRM Connector";
        $y++;
        $apps[$x]['default_settings'][$y]['default_setting_uuid'] = "13bd2d3a-3465-4859-8b00-8d73e16a1b8c";
		$apps[$x]['default_settings'][$y]['default_setting_category'] = "vtiger_connector";
		$apps[$x]['default_settings'][$y]['default_setting_subcategory'] = "url";
		$apps[$x]['default_settings'][$y]['default_setting_name'] = "text";
		$apps[$x]['default_settings'][$y]['default_setting_value'] = "";
		$apps[$x]['default_settings'][$y]['default_setting_enabled'] = "false";
		$apps[$x]['default_settings'][$y]['default_setting_description'] = "VTiger CRM URL";
        $y++;
        $apps[$x]['default_settings'][$y]['default_setting_uuid'] = "ad1fefbb-536f-4083-bb1f-fd1bcad6ff2f";
		$apps[$x]['default_settings'][$y]['default_setting_category'] = "vtiger_connector";
		$apps[$x]['default_settings'][$y]['default_setting_subcategory'] = "api_key";
		$apps[$x]['default_settings'][$y]['default_setting_name'] = "text";
		$apps[$x]['default_settings'][$y]['default_setting_value'] = "";
		$apps[$x]['default_settings'][$y]['default_setting_enabled'] = "false";
		$apps[$x]['default_settings'][$y]['default_setting_description'] = "VTiger API";
        $y++;
        $apps[$x]['default_settings'][$y]['default_setting_uuid'] = "f611c8f4-2e62-4146-ba47-71f9d78fd77e";
		$apps[$x]['default_settings'][$y]['default_setting_category'] = "vtiger_connector";
		$apps[$x]['default_settings'][$y]['default_setting_subcategory'] = "record_path";
		$apps[$x]['default_settings'][$y]['default_setting_name'] = "text";
		$apps[$x]['default_settings'][$y]['default_setting_value'] = "";
		$apps[$x]['default_settings'][$y]['default_setting_enabled'] = "false";
		$apps[$x]['default_settings'][$y]['default_setting_description'] = "VTiger Record path";
		$y++;
?>
