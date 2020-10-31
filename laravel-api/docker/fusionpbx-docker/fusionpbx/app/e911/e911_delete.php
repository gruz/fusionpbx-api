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
	Portions created by the Initial Developer are Copyright (C) 2008-2012
	the Initial Developer. All Rights Reserved.

	Contributor(s):
	Mark J Crane <markjcrane@fusionpbx.com>
*/
require_once "root.php";
require_once "resources/require.php";
require_once "resources/check_auth.php";

require_once "app/e911/api_calls.php";

if (permission_exists('e911_delete')) {
	//access granted
}
else {
	echo "access denied";
	exit;
}

//add multi-lingual support
	$language = new text;
	$text = $language->get();

if (count($_GET)>0) {
	$id = escape(check_str($_GET["id"]));
}

$message = $text['message-delete'];

if (strlen($id)>0) {
	// First - get did for this call and call API
	$sql = "select e911_did from v_e911 ";
	$sql .= "where domain_uuid = '$domain_uuid' ";
	$sql .= "and e911_uuid = '$id' ";
	$prep_statement = $db->prepare(check_sql($sql));
	$prep_statement->execute();
	$did = $prep_statement->fetch(PDO::FETCH_ASSOC);
	unset ($sql, $prep_statement);

	$did = isset($did['e911_did'])?$did['e911_did']:False;
	if ($did) {
		$did = array_map('escape', $did);
		if (remove_e911_data($did)) {
			$message .= " ".$text['message-e911_info_deleted'];
		} else {
			$message .= " ".$text['message-e911_info_deleted_error'];
		}
	} else {
		$message .= " ".$text['message-e911_info_deleted_error'];
	}

	// Delete data from database
	$sql = "delete from v_e911 ";
	$sql .= "where domain_uuid = '$domain_uuid' ";
	$sql .= "and e911_uuid = '$id' ";
	$prep_statement = $db->prepare(check_sql($sql));
	$prep_statement->execute();
	unset($sql);
}


$_SESSION["message"] = $message;
header("Location: e911.php");
return;

?>
