<?php
/*
	FusionPBX
	Version: MPL 1.1

	The contents of this file are subject to the Mozilla Public License Version
	1.1 (the "License"); you may not use this file except in compliance with
	the License. You may obtain a copy of the License at
	http://www.mozilla.org/MPL/

	Software distributed under the License is distributed on an "AS IS" basis,
	WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License
	for the specific language governing rights and limitations under the
	License.

	The Original Code is FusionPBX

	The Initial Developer of the Original Code is
	Mark J Crane <markjcrane@fusionpbx.com>
	Portions created by the Initial Developer are Copyright (C) 2008-2016
	the Initial Developer. All Rights Reserved.

	Contributor(s):
	Mark J Crane <markjcrane@fusionpbx.com>
*/

//includes
	include "root.php";
	require_once "resources/require.php";
	require_once "resources/check_auth.php";
	require_once "resources/paging.php";

//check permissions
	if (permission_exists('dialplan_add')
		|| permission_exists('inbound_route_add')
		|| permission_exists('outbound_route_add')
		|| permission_exists('time_condition_add')) {
		//access granted
	}
	else {
		echo "access denied";
		exit;
	}

//add multi-lingual support
	$language = new text;
	$text = $language->get();

//set the http get/post variable(s) to a php variable
	if (isset($_REQUEST["id"])) {
		$sip_profile_uuid = check_str($_REQUEST["id"]);
		$sip_profile_name = check_str($_REQUEST["name"]);
	}

//get the sip profile data
	if (count($_GET) > 0 && $_POST["persistformvar"] != "true") {
		$sql = "select * from v_sip_profiles ";
		$sql .= "where sip_profile_uuid = '$sip_profile_uuid' ";
		$prep_statement = $db->prepare(check_sql($sql));
		$prep_statement->execute();
		$result = $prep_statement->fetchAll();
		foreach ($result as &$row) {
			$sip_profile_description = $row["sip_profile_description"];
		}
		unset ($prep_statement);
	}

//copy the v_sip_profiles
	$sip_profile_uuid_new = uuid();
	$sip_profile_enabled = 'true';
	$sql = "insert into v_sip_profiles ";
	$sql .= "(";
	$sql .= "sip_profile_uuid, ";
	$sql .= "sip_profile_name, ";
	$sql .= "sip_profile_enabled, ";
	$sql .= "sip_profile_description ";
	$sql .= ")";
	$sql .= "values ";
	$sql .= "(";
	$sql .= "'".$sip_profile_uuid_new."', ";
	$sql .= "'".$sip_profile_name."', ";
	$sql .= "'".$sip_profile_enabled."', ";
	$sql .= "'".$sip_profile_description."' ";
	$sql .= ")";
	$db->exec(check_sql($sql));
	unset($sql);

//get the the sip profile settings
	$sql = "select * from v_sip_profile_domains ";
	$sql .= "where sip_profile_uuid = '$sip_profile_uuid' ";
	$prep_statement = $db->prepare(check_sql($sql));
	$prep_statement->execute();
	$result = $prep_statement->fetchAll();
	foreach ($result as &$row) {
		$sip_profile_domain_name = $row["sip_profile_domain_name"];
		$sip_profile_domain_alias = $row["sip_profile_domain_alias"];
		$sip_profile_domain_parse = $row["sip_profile_domain_parse"];

		//add the sip profile setting
			$sql = "insert into v_sip_profile_domains ";
			$sql .= "(";
			$sql .= "sip_profile_domain_uuid, ";
			$sql .= "sip_profile_uuid, ";
			$sql .= "sip_profile_domain_name, ";
			$sql .= "sip_profile_domain_alias, ";
			$sql .= "sip_profile_domain_parse ";
			$sql .= ")";
			$sql .= "values ";
			$sql .= "(";
			$sql .= "'".uuid()."', ";
			$sql .= "'$sip_profile_uuid_new', ";
			$sql .= "'$sip_profile_domain_name', ";
			$sql .= "'$sip_profile_domain_alias', ";
			$sql .= "'$sip_profile_domain_parse' ";
			$sql .= ")";
			$db->exec(check_sql($sql));
			unset($sql);
	}
	unset ($prep_statement);

//get the the sip profile settings
	$sql = "select * from v_sip_profile_settings ";
	$sql .= "where sip_profile_uuid = '$sip_profile_uuid' ";
	$prep_statement = $db->prepare(check_sql($sql));
	$prep_statement->execute();
	$result = $prep_statement->fetchAll();
	foreach ($result as &$row) {
		$sip_profile_setting_name = $row["sip_profile_setting_name"];
		$sip_profile_setting_value = $row["sip_profile_setting_value"];
		$sip_profile_setting_enabled = $row["sip_profile_setting_enabled"];
		$sip_profile_setting_description = $row["sip_profile_setting_description"];

		//add the sip profile setting
			$sql = "insert into v_sip_profile_settings ";
			$sql .= "(";
			$sql .= "sip_profile_setting_uuid, ";
			$sql .= "sip_profile_uuid, ";
			$sql .= "sip_profile_setting_name, ";
			$sql .= "sip_profile_setting_value, ";
			$sql .= "sip_profile_setting_enabled, ";
			$sql .= "sip_profile_setting_description ";
			$sql .= ")";
			$sql .= "values ";
			$sql .= "(";
			$sql .= "'".uuid()."', ";
			$sql .= "'$sip_profile_uuid_new', ";
			$sql .= "'$sip_profile_setting_name', ";
			$sql .= "'$sip_profile_setting_value', ";
			$sql .= "'$sip_profile_setting_enabled', ";
			$sql .= "'$sip_profile_setting_description' ";
			$sql .= ")";
			$db->exec(check_sql($sql));
			unset($sql);
	}
	unset ($prep_statement);

//save the sip profile xml
	save_sip_profile_xml();

//apply settings reminder
	$_SESSION["reload_xml"] = true;

//redirect the user
	messages::add($text['message-copy']);
	header("Location: ".PROJECT_PATH."/app/sip_profiles/sip_profiles.php");
	return;

?>
