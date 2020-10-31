<?php

	//application details
		$apps[$x]['name'] = "Phonebook";
		$apps[$x]['uuid'] = "63dadfd0-ec76-11e6-b006-92361f002671";
		$apps[$x]['category'] = "Switch";
		$apps[$x]['subcategory'] = "";
		$apps[$x]['version'] = "";
		$apps[$x]['license'] = "Mozilla Public License 1.1";
		$apps[$x]['url'] = "http://www.fusionpbx.com";
		$apps[$x]['description']['en-us'] = "A tool to implement a Phonebook.";
		$apps[$x]['description']['es-cl'] = "A tool to implement a Phonebook.";
		$apps[$x]['description']['de-de'] = "";
		$apps[$x]['description']['de-ch'] = "";
		$apps[$x]['description']['de-at'] = "";
		$apps[$x]['description']['fr-fr'] = "A tool to implement a Phonebook.";
		$apps[$x]['description']['fr-ca'] = "";
		$apps[$x]['description']['fr-ch'] = "";
		$apps[$x]['description']['pt-pt'] = "A tool to implement a Phonebook.";
		$apps[$x]['description']['pt-br'] = "A tool to implement a Phonebook.";

	//permission details
		$y = 0;
		$apps[$x]['permissions'][$y]['name'] = "phonebook_view";
		$apps[$x]['permissions'][$y]['menu']['uuid'] = "54e525be-ec76-11e6-b006-92361f002671";
		$apps[$x]['permissions'][$y]['groups'][] = "superadmin";
		$apps[$x]['permissions'][$y]['groups'][] = "admin";
		$y += 1;
		$apps[$x]['permissions'][$y]['name'] = "phonebook_add";
		$apps[$x]['permissions'][$y]['groups'][] = "superadmin";
		$apps[$x]['permissions'][$y]['groups'][] = "admin";
		$y += 1;
		$apps[$x]['permissions'][$y]['name'] = "phonebook_edit";
		$apps[$x]['permissions'][$y]['groups'][] = "superadmin";
		$apps[$x]['permissions'][$y]['groups'][] = "admin";
		$y += 1;
		$apps[$x]['permissions'][$y]['name'] = "phonebook_delete";
		$apps[$x]['permissions'][$y]['groups'][] = "superadmin";
		$apps[$x]['permissions'][$y]['groups'][] = "admin";
                $y += 1;
                $apps[$x]['permissions'][$y]['name'] = "phonebook_group_add";
                $apps[$x]['permissions'][$y]['groups'][] = "superadmin";
                $apps[$x]['permissions'][$y]['groups'][] = "admin";
                $y += 1;
                $apps[$x]['permissions'][$y]['name'] = "phonebook_group_edit";
                $apps[$x]['permissions'][$y]['groups'][] = "superadmin";
                $apps[$x]['permissions'][$y]['groups'][] = "admin";
                $y += 1;
                $apps[$x]['permissions'][$y]['name'] = "phonebook_group_delete";
                $apps[$x]['permissions'][$y]['groups'][] = "superadmin";
                $apps[$x]['permissions'][$y]['groups'][] = "admin";
                $y += 1;
                $apps[$x]['permissions'][$y]['name'] = "phonebook_import";
                $apps[$x]['permissions'][$y]['groups'][] = "superadmin";
                $apps[$x]['permissions'][$y]['groups'][] = "admin";
                $y += 1;
                $apps[$x]['permissions'][$y]['name'] = "phonebook_phone_access";
                $apps[$x]['permissions'][$y]['groups'][] = "superadmin";
                $apps[$x]['permissions'][$y]['groups'][] = "admin";
                $y += 1;

                $y = 0;
		$apps[$x]['default_settings'][$y]['default_setting_uuid'] = "1810d79c-c371-497d-9f13-b52a0879d2da";
		$apps[$x]['default_settings'][$y]['default_setting_category'] = "phonebook";
		$apps[$x]['default_settings'][$y]['default_setting_subcategory'] = "auth";
		$apps[$x]['default_settings'][$y]['default_setting_name'] = "text";
		$apps[$x]['default_settings'][$y]['default_setting_value'] = "true";
		$apps[$x]['default_settings'][$y]['default_setting_enabled'] = "true";
                $apps[$x]['default_settings'][$y]['default_setting_description'] = "Set if you don't want to use API-key for authorization. Less secure";
		$y++;
		$apps[$x]['default_settings'][$y]['default_setting_uuid'] = 'e90cdfeb-8360-4f36-b826-4a2feb470066';
		$apps[$x]['default_settings'][$y]['default_setting_category'] = 'phonebook';
		$apps[$x]['default_settings'][$y]['default_setting_subcategory'] = 'snom_embedded_settings';
		$apps[$x]['default_settings'][$y]['default_setting_name'] = 'text';
		$apps[$x]['default_settings'][$y]['default_setting_value'] = 'false';
		$apps[$x]['default_settings'][$y]['default_setting_enabled'] = 'false';
		$apps[$x]['default_settings'][$y]['default_setting_description'] = 'Include XML header on result answer or not';
                $y++;
                $apps[$x]['default_settings'][$y]['default_setting_uuid'] = '060d1dba-2151-4d54-ba15-b3c5f4a8d09b';
		$apps[$x]['default_settings'][$y]['default_setting_category'] = 'phonebook';
		$apps[$x]['default_settings'][$y]['default_setting_subcategory'] = 'polycom_speed_dial_show';
		$apps[$x]['default_settings'][$y]['default_setting_name'] = 'text';
		$apps[$x]['default_settings'][$y]['default_setting_value'] = 'false';
		$apps[$x]['default_settings'][$y]['default_setting_enabled'] = 'false';
		$apps[$x]['default_settings'][$y]['default_setting_description'] = 'Add speed dial entity into phonebook. With enabling this you will see your contact list on a main screen on free buttons';
		$y++;

	        //schema details
                $y = 0; //table array index
                $z = 0; //field array index

                // Main table with entries

                $apps[$x]['db'][$y]['table']['name'] = "v_phonebook";
                $apps[$x]['db'][$y]['table']['parent'] = "";

                $apps[$x]['db'][$y]['fields'][$z]['name'] = "domain_uuid";
                $apps[$x]['db'][$y]['fields'][$z]['type']['pgsql'] = "uuid";
                $apps[$x]['db'][$y]['fields'][$z]['type']['sqlite'] = "text";
                $apps[$x]['db'][$y]['fields'][$z]['type']['mysql'] = "char(36)";
                $apps[$x]['db'][$y]['fields'][$z]['key']['type'] = "foreign";
                $apps[$x]['db'][$y]['fields'][$z]['key']['reference']['table'] = "v_domains";
                $apps[$x]['db'][$y]['fields'][$z]['key']['reference']['field'] = "domain_uuid";
                $z += 1;
                $apps[$x]['db'][$y]['fields'][$z]['name']['text'] = "phonebook_uuid";
                $apps[$x]['db'][$y]['fields'][$z]['type']['pgsql'] = "uuid";
                $apps[$x]['db'][$y]['fields'][$z]['type']['sqlite'] = "text";
                $apps[$x]['db'][$y]['fields'][$z]['type']['mysql'] = "char(36)";
                $apps[$x]['db'][$y]['fields'][$z]['key']['type'] = "primary";
                $z += 1;
                $apps[$x]['db'][$y]['fields'][$z]['name']['text'] = "name";
                $apps[$x]['db'][$y]['fields'][$z]['type'] = "text";
                $apps[$x]['db'][$y]['fields'][$z]['description']['en'] = "Enter the name.";
                $apps[$x]['db'][$y]['fields'][$z]['description']['pt-br'] = "Enter the name.";
                $z += 1;
                $apps[$x]['db'][$y]['fields'][$z]['name']['text'] = "phonenumber";
                $apps[$x]['db'][$y]['fields'][$z]['type'] = "text";
                $apps[$x]['db'][$y]['fields'][$z]['description']['en'] = "Enter the phone number.";
                $apps[$x]['db'][$y]['fields'][$z]['description']['pt-br'] = "Insira o número de telefone.";
                $z += 1;
                $apps[$x]['db'][$y]['fields'][$z]['name']['text'] = "phonebook_desc";
                $apps[$x]['db'][$y]['fields'][$z]['type'] = "text";
                $apps[$x]['db'][$y]['fields'][$z]['description']['en'] = "Enter the description.";
                $apps[$x]['db'][$y]['fields'][$z]['description']['pt-br'] = "Enter the description.";

                $y += 1; //table array index
                $z = 0; //field array index
                // Groups description
                $apps[$x]['db'][$y]['table']['name'] = "v_phonebook_groups";
                $apps[$x]['db'][$y]['table']['parent'] = "v_phonebook";

                $apps[$x]['db'][$y]['fields'][$z]['name'] = "domain_uuid";
                $apps[$x]['db'][$y]['fields'][$z]['type']['pgsql'] = "uuid";
                $apps[$x]['db'][$y]['fields'][$z]['type']['sqlite'] = "text";
                $apps[$x]['db'][$y]['fields'][$z]['type']['mysql'] = "char(36)";
                $apps[$x]['db'][$y]['fields'][$z]['key']['type'] = "foreign";
                $apps[$x]['db'][$y]['fields'][$z]['key']['reference']['table'] = "v_domains";
                $apps[$x]['db'][$y]['fields'][$z]['key']['reference']['field'] = "domain_uuid";
                $apps[$x]['db'][$y]['fields'][$z]['description']['en-us'] = "";
                $z += 1;
                $apps[$x]['db'][$y]['fields'][$z]['name']['text'] = "group_uuid";
                $apps[$x]['db'][$y]['fields'][$z]['type']['pgsql'] = "uuid";
                $apps[$x]['db'][$y]['fields'][$z]['type']['sqlite'] = "text";
                $apps[$x]['db'][$y]['fields'][$z]['type']['mysql'] = "char(36)";
                $apps[$x]['db'][$y]['fields'][$z]['key']['type'] = "primary";
                $z += 1;
                $apps[$x]['db'][$y]['fields'][$z]['name']['text'] = "group_name";
                $apps[$x]['db'][$y]['fields'][$z]['type'] = "text";
                $apps[$x]['db'][$y]['fields'][$z]['description']['en'] = "Enter the name.";
                $apps[$x]['db'][$y]['fields'][$z]['description']['pt-br'] = "Enter the name.";
                $z += 1;
                $apps[$x]['db'][$y]['fields'][$z]['name']['text'] = "group_desc";
                $apps[$x]['db'][$y]['fields'][$z]['type'] = "text";
                $apps[$x]['db'][$y]['fields'][$z]['description']['en'] = "Enter the description.";
                $apps[$x]['db'][$y]['fields'][$z]['description']['pt-br'] = "Enter the description.";


                $y += 1; //table array index
                $z = 0; //field array index
                // Link between groups and phonebook entries. Classic many-to-many
                $apps[$x]['db'][$y]['table']['name'] = "v_phonebook_to_groups";
                $apps[$x]['db'][$y]['table']['parent'] = "v_phonebook";

                $apps[$x]['db'][$y]['fields'][$z]['name'] = "domain_uuid";
                $apps[$x]['db'][$y]['fields'][$z]['type']['pgsql'] = "uuid";
                $apps[$x]['db'][$y]['fields'][$z]['type']['sqlite'] = "text";
                $apps[$x]['db'][$y]['fields'][$z]['type']['mysql'] = "char(36)";
                $apps[$x]['db'][$y]['fields'][$z]['key']['type'] = "foreign";
                $apps[$x]['db'][$y]['fields'][$z]['key']['reference']['table'] = "v_domains";
                $apps[$x]['db'][$y]['fields'][$z]['key']['reference']['field'] = "domain_uuid";
                $apps[$x]['db'][$y]['fields'][$z]['description']['en-us'] = "";
                $z += 1;
                $apps[$x]['db'][$y]['fields'][$z]['name']['text'] = "phonebook_uuid";
                $apps[$x]['db'][$y]['fields'][$z]['type']['pgsql'] = "uuid";
                $apps[$x]['db'][$y]['fields'][$z]['type']['sqlite'] = "text";
                $apps[$x]['db'][$y]['fields'][$z]['type']['mysql'] = "char(36)";
                $apps[$x]['db'][$y]['fields'][$z]['key']['type'] = "foreign";
                $apps[$x]['db'][$y]['fields'][$z]['key']['reference']['table'] = "v_phonebook";
                $apps[$x]['db'][$y]['fields'][$z]['key']['reference']['field'] = "phonebook_uuid";
                $z += 1;
                $apps[$x]['db'][$y]['fields'][$z]['name']['text'] = "group_uuid";
                $apps[$x]['db'][$y]['fields'][$z]['type']['pgsql'] = "uuid";
                $apps[$x]['db'][$y]['fields'][$z]['type']['sqlite'] = "text";
                $apps[$x]['db'][$y]['fields'][$z]['type']['mysql'] = "char(36)";
                $apps[$x]['db'][$y]['fields'][$z]['key']['type'] = "foreign";
                $apps[$x]['db'][$y]['fields'][$z]['key']['reference']['table'] = "v_phonebook_groups";
                $apps[$x]['db'][$y]['fields'][$z]['key']['reference']['field'] = "group_uuid";
?>
