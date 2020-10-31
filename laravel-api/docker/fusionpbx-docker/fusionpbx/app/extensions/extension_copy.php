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
	if (permission_exists('extension_copy')) {
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
	if (isset($_REQUEST["id"]) && isset($_REQUEST["ext"])) {
		$extension_uuid = check_str($_REQUEST["id"]);
		$extension_new = check_str($_REQUEST["ext"]);
		if (!is_numeric($extension_new)) {
			$number_alias_new = check_str($_REQUEST["alias"]);
		}
	}
	
// skip the copy if the domain extension already exists
	$extension = new extension;
	if ($extension->exists($_SESSION['domain_uuid'], $extension_new)) {
		messages::add($text['message-duplicate'], 'negative');
		header("Location: extensions.php");
		return;
	}
	
//get the v_extensions data
	$sql = "select * from v_extensions ";
	$sql .= "where domain_uuid = '$domain_uuid' ";
	$sql .= "and extension_uuid = '$extension_uuid' ";
	$prep_statement = $db->prepare(check_sql($sql));
	$prep_statement->execute();
	$result = $prep_statement->fetchAll(PDO::FETCH_NAMED);
	foreach ($result as &$row) {
		$extension = $row["extension"];
		$number_alias = $row["number_alias"];
		$accountcode = $row["accountcode"];
		$effective_caller_id_name = $row["effective_caller_id_name"];
		$effective_caller_id_number = $row["effective_caller_id_number"];
		$outbound_caller_id_name = $row["outbound_caller_id_name"];
		$outbound_caller_id_number = $row["outbound_caller_id_number"];
		$emergency_caller_id_name = $row["emergency_caller_id_name"];
		$emergency_caller_id_number = $row["emergency_caller_id_number"];
		$directory_visible = $row["directory_visible"];
		$directory_exten_visible = $row["directory_exten_visible"];
		$limit_max = $row["limit_max"];
		$limit_destination = $row["limit_destination"];
		$user_context = $row["user_context"];
		$missed_call_app = $row["missed_call_app"];
		$missed_call_data = $row["missed_call_data"];
		$toll_allow = $row["toll_allow"];
		$call_timeout = $row["call_timeout"];
		$call_group = $row["call_group"];
		$user_record = $row["user_record"];
		$hold_music = $row["hold_music"];
		$auth_acl = $row["auth_acl"];
		$cidr = $row["cidr"];
		$sip_force_contact = $row["sip_force_contact"];
		$nibble_account = $row["nibble_account"];
		$sip_force_expires = $row["sip_force_expires"];
		$mwi_account = $row["mwi_account"];
		$sip_bypass_media = $row["sip_bypass_media"];
		$dial_string = $row["dial_string"];
		$enabled = $row["enabled"];
		$description = $text['button-copy'].': '.$row["description"];
	}
	unset ($prep_statement);

//copy the extension
	$extension_uuid = uuid();
	$password = generate_password();
	$sql = "insert into v_extensions ";
	$sql .= "(";
	$sql .= "domain_uuid, ";
	$sql .= "extension_uuid, ";
	$sql .= "extension, ";
	$sql .= "number_alias, ";
	$sql .= "password, ";
	$sql .= "accountcode, ";
	$sql .= "effective_caller_id_name, ";
	$sql .= "effective_caller_id_number, ";
	$sql .= "outbound_caller_id_name, ";
	$sql .= "outbound_caller_id_number, ";
	$sql .= "emergency_caller_id_name, ";
	$sql .= "emergency_caller_id_number, ";
	$sql .= "directory_visible, ";
	$sql .= "directory_exten_visible, ";
	$sql .= "limit_max, ";
	$sql .= "limit_destination, ";
	$sql .= "user_context, ";
	$sql .= "missed_call_app, ";
	$sql .= "missed_call_data, ";
	$sql .= "toll_allow, ";
	$sql .= "call_timeout, ";
	$sql .= "call_group, ";
	$sql .= "user_record, ";
	$sql .= "hold_music, ";
	$sql .= "auth_acl, ";
	$sql .= "cidr, ";
	$sql .= "sip_force_contact, ";
	$sql .= "nibble_account, ";
	$sql .= "sip_force_expires, ";
	$sql .= "mwi_account, ";
	$sql .= "sip_bypass_media, ";
	$sql .= "dial_string, ";
	$sql .= "enabled, ";
	$sql .= "description ";
	$sql .= ")";
	$sql .= "values ";
	$sql .= "(";
	$sql .= "'$domain_uuid', ";
	$sql .= "'$extension_uuid', ";
	$sql .= "'$extension_new', ";
	$sql .= "'$number_alias_new', ";
	$sql .= "'$password', ";
	$sql .= "'$accountcode', ";
	$sql .= "'$effective_caller_id_name', ";
	$sql .= "'$effective_caller_id_number', ";
	$sql .= "'$outbound_caller_id_name', ";
	$sql .= "'$outbound_caller_id_number', ";
	$sql .= "'$emergency_caller_id_name', ";
	$sql .= "'$emergency_caller_id_number', ";
	$sql .= "'$directory_visible', ";
	$sql .= "'$directory_exten_visible', ";
	if (strlen($limit_max) > 0) { $sql .= "'$limit_max', "; } else { $sql .= "null, "; }
	$sql .= "'$limit_destination', ";
	$sql .= "'$user_context', ";
	$sql .= "'$missed_call_app', ";
	$sql .= "'$missed_call_data', ";
	$sql .= "'$toll_allow', ";
	if (strlen($call_timeout) > 0) { $sql .= "'$call_timeout', "; } else { $sql .= "null, "; }
	$sql .= "'$call_group', ";
	$sql .= "'$user_record', ";
	$sql .= "'$hold_music', ";
	$sql .= "'$auth_acl', ";
	$sql .= "'$cidr', ";
	$sql .= "'$sip_force_contact', ";
	if (strlen($nibble_account) > 0) { $sql .= "'$nibble_account', "; } else { $sql .= "null, "; }
	if (strlen($sip_force_expires) > 0) { $sql .= "'$sip_force_expires', "; } else { $sql .= "null, "; }
	$sql .= "'$mwi_account', ";
	$sql .= "'$sip_bypass_media', ";
	$sql .= "'$dial_string', ";
	$sql .= "'$enabled', ";
	$sql .= "'$description' ";
	$sql .= ")";
	$db->exec(check_sql($sql));
	unset($sql);

//get the source extension voicemail data
	if (is_dir($_SERVER["DOCUMENT_ROOT"].PROJECT_PATH.'/app/voicemails')) {

		//get the voicemails
			$sql = "select * from v_voicemails ";
			$sql .= "where domain_uuid = '$domain_uuid' ";
			if (is_numeric($number_alias)) {
				$sql .= "and voicemail_id = '$number_alias' ";
			}
			else {
				$sql .= "and voicemail_id = '$extension' ";
			}
			$prep_statement = $db->prepare(check_sql($sql));
			$prep_statement->execute();
			$result = $prep_statement->fetchAll(PDO::FETCH_NAMED);
			foreach ($result as &$row) {
				$voicemail_mailto = $row["voicemail_mail_to"];
				$voicemail_file = $row["voicemail_file"];
				$voicemail_local_after_email = $row["voicemail_local_after_email"];
				$voicemail_enabled = $row["voicemail_enabled"];
			}
			unset ($prep_statement);

		//set the new voicemail password
			if (strlen($voicemail_password) == 0) {
				$voicemail_password = generate_password(9, 1);
			}

		//add voicemail via class
			$ext = new extension;
			$ext->db = $db;
			$ext->domain_uuid = $domain_uuid;
			$ext->extension = $extension_new;
			$ext->number_alias = $number_alias_new;
			$ext->voicemail_password = $voicemail_password;
			$ext->voicemail_mail_to = $voicemail_mailto;
			$ext->voicemail_file = $voicemail_file;
			$ext->voicemail_local_after_email = $voicemail_local_after_email;
			$ext->voicemail_enabled = $voicemail_enabled;
			$ext->description = $description;
			$ext->voicemail();
			unset($ext);

	}

//synchronize configuration
	if (is_writable($_SESSION['switch']['extensions']['dir'])) {
		require_once "app/extensions/resources/classes/extension.php";
		$ext = new extension;
		$ext->xml();
		unset($ext);
	}

//redirect the user
	messages::add($text['message-copy']);
	header("Location: extensions.php");
	return;

?>
