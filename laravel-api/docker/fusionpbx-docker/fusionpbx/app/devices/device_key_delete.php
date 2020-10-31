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
	Copyright (C) 2015 All Rights Reserved.

	Contributor(s):
	Mark J Crane <markjcrane@fusionpbx.com>
*/
require_once "root.php";
require_once "resources/require.php";
require_once "resources/check_auth.php";
if (permission_exists('device_key_delete')) {
	//access granted
}
else {
	echo "access denied";
	exit;
}

//add multi-lingual support
	$language = new text;
	$text = $language->get();

//get the id
	if (isset($_GET["id"])) {
		$id = $_GET["id"];
		$device_uuid = check_str($_GET["device_uuid"]);
		$device_profile_uuid = check_str($_GET["device_profile_uuid"]);
	}

//delete device keys
	if (is_uuid($id)) {
		$sql = "delete from v_device_keys ";
		$sql .= "where (domain_uuid = '".$_SESSION["domain_uuid"]."' or domain_uuid is null) ";
		$sql .= "and device_key_uuid = '".$id."' ";
		$db->exec($sql);
		unset($sql);
	}

//send a redirect
	messages::add($text['message-delete']);
	if ($device_uuid != '') {
		header("Location: device_edit.php?id=".$device_uuid);
	}
	else if ($device_profile_uuid != '') {
		header("Location: device_profile_edit.php?id=".$device_profile_uuid);
	}
	return;
?>