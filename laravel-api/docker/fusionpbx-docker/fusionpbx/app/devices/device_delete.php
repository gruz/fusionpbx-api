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
	Copyright (C) 2008-2016 All Rights Reserved.

	Contributor(s):
	Mark J Crane <markjcrane@fusionpbx.com>
*/

//includes
	require_once "root.php";
	require_once "resources/require.php";
	require_once "resources/check_auth.php";

//check permissions
	if (permission_exists('device_delete')) {
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
	}

//delete the data and sub-data
	if (is_uuid($id)) {

		//delete device_lines
			$sql = "delete from v_device_lines ";
			$sql .= "where device_uuid = '$id' ";
			$db->exec($sql);
			unset($sql);

		//delete device_keys
			$sql = "delete from v_device_keys ";
			$sql .= "where device_uuid = '$id' ";
			$db->exec($sql);
			unset($sql);

		//delete device_settings
			$sql = "delete from v_device_settings ";
			$sql .= "where device_uuid = '$id' ";
			$db->exec($sql);
			unset($sql);

		//delete the device
			$sql = "delete from v_devices ";
			$sql .= "where device_uuid = '$id' ";
			$db->exec($sql);
			unset($sql);
	}

//write the provision files
	if (strlen($_SESSION['provision']['path']['text']) > 0) {
		$prov = new provision;
		$prov->domain_uuid = $domain_uuid;
		$response = $prov->write();
	}

//set the message and redirect the user
	messages::add($text['message-delete']);
	header("Location: devices.php");
	return;

?>
