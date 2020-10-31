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
	require_once "root.php";
	require_once "resources/require.php";
	require_once "resources/check_auth.php";

//check permissions
	if (permission_exists('call_center_queue_delete')) {
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
	if (count($_GET) > 0) {
		$id = check_str($_GET["id"]);
	}

//delete the data
	if (strlen($id) > 0) {

		//get the dialplan uuid
			$sql = "select * from v_call_center_queues ";
			$sql .= "where domain_uuid = '$domain_uuid' ";
			$sql .= "and call_center_queue_uuid = '$id' ";
			$prep_statement = $db->prepare($sql);
			$prep_statement->execute();
			while($row = $prep_statement->fetch(PDO::FETCH_ASSOC)) {
				$queue_name = $row['queue_name'];
				$dialplan_uuid = $row['dialplan_uuid'];
			}

		//delete the tier from the database
			$sql = "delete from v_call_center_tiers ";
			$sql .= "where domain_uuid = '$domain_uuid' ";
			$sql .= "and (call_center_queue_uuid = '$id' or queue_name = '".$queue_name."@".$_SESSION['domain_name']."') ";
			$db->query($sql);
			unset($sql);

		//delete the call center queue
			$sql = "delete from v_call_center_queues ";
			$sql .= "where domain_uuid = '$domain_uuid' ";
			$sql .= "and call_center_queue_uuid = '$id' ";
			$db->query($sql);
			unset($sql);

		//delete the dialplan entry
			$sql = "delete from v_dialplans ";
			$sql .= "where domain_uuid = '$domain_uuid' ";
			$sql .= "and dialplan_uuid = '$dialplan_uuid' ";
			$db->query($sql);
			unset($sql);

		//delete the dialplan details
			$sql = "delete from v_dialplan_details ";
			$sql .= "where domain_uuid = '$domain_uuid' ";
			$sql .= "and dialplan_uuid = '$dialplan_uuid' ";
			$db->query($sql);
			unset($sql);

		//clear the cache
			$cache = new cache;
			$cache->delete("dialplan:".$_SESSION["context"]);
			remove_config_from_cache('configuration:callcenter.conf');

		//synchronize configuration
			save_dialplan_xml();
			save_call_center_xml();

		//apply settings reminder
			$_SESSION["reload_xml"] = true;
	}

//redirect the browser
	messages::add($text['message-delete']);
	header("Location: call_center_queues.php");
	return;

?>
