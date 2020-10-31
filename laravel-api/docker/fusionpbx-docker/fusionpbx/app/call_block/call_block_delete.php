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

	Call Block is written by Gerrit Visser <gerrit308@gmail.com>
*/
//includes
	include "root.php";
	require_once "resources/require.php";
	require_once "resources/check_auth.php";

//check permissions
	if (permission_exists('call_block_delete')) {
		//access granted
	}
	else {
		echo "access denied";
		exit;
	}

//add multi-lingual support
	$language = new text;
	$text = $language->get();

//set the variable
	if (count($_GET)>0) {
		$id = $_GET["id"];
	}

//delete the extension
	if (strlen($id)>0) {
		//read the call_block_number
			$sql = " select c.call_block_number, d.domain_name from v_call_block as c ";
			$sql .= "JOIN v_domains as d ON c.domain_uuid=d.domain_uuid ";
			$sql .= "where c.domain_uuid = '".$_SESSION['domain_uuid']."' ";
			$sql .= "and c.call_block_uuid = '$id' ";
			$prep_statement = $db->prepare(check_sql($sql));
			$prep_statement->execute();
			$result = $prep_statement->fetchAll();
			$result_count = count($result);
			if ($result_count > 0) {
				$call_block_number = $result[0]["call_block_number"];
				$domain_name = $result[0]["domain_name"];

				//clear the cache
				$cache = new cache;
				$cache->delete("app:call_block:".$domain_name.":".$call_block_number);
			}
			unset ($prep_statement, $sql);

		//delete the call block
			$sql = "delete from v_call_block ";
			$sql .= "where domain_uuid = '".$_SESSION['domain_uuid']."' ";
			$sql .= "and call_block_uuid = '$id' ";
			$prep_statement = $db->prepare(check_sql($sql));
			$prep_statement->execute();
			unset($prep_statement, $sql);
	}

	//redirect the browser
		messages::add($text['label-delete-complete']);
		header("Location: call_block.php");
		return;

?>
