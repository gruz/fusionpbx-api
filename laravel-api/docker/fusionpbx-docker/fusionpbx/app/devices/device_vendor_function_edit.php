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
	Portions created by the Initial Developer are Copyright (C) 2016
	the Initial Developer. All Rights Reserved.

	Contributor(s):
	Mark J Crane <markjcrane@fusionpbx.com>
*/

//includes
	require_once "root.php";
	require_once "resources/require.php";

//delete the group from the menu item
	if ($_REQUEST["a"] == "delete" && permission_exists("device_vendor_function_delete") && $_REQUEST["id"] != '') {
		//get the id
			$device_vendor_function_group_uuid = check_str($_REQUEST["id"]);
			$device_vendor_function_uuid = check_str($_REQUEST["device_vendor_function_uuid"]);
			$device_vendor_uuid = check_str($_REQUEST["device_vendor_uuid"]);
		//delete the group from the users
			$sql = "delete from v_device_vendor_function_groups ";
			$sql .= "where device_vendor_function_group_uuid = '".$device_vendor_function_group_uuid."' ";
			$db->exec(check_sql($sql));
		//redirect the browser
			messages::add($text['message-delete']);
			header("Location: device_vendor_function_edit.php?id=".escape($device_vendor_function_uuid) ."&device_vendor_uuid=".escape($device_vendor_uuid));
			return;
	}

//check permissions
	require_once "resources/check_auth.php";
	if (permission_exists('device_vendor_function_add') || permission_exists('device_vendor_function_edit')) {
		//access granted
	}
	else {
		echo "access denied";
		exit;
	}

//action add or update
	if (isset($_REQUEST["id"])) {
		$action = "update";
		$device_vendor_function_uuid = check_str($_REQUEST["id"]);
	}
	else {
		$action = "add";
	}

//add multi-lingual support
	$language = new text;
	$text = $language->get();

//set the parent uuid
	if (strlen($_GET["device_vendor_uuid"]) > 0) {
		$device_vendor_uuid = check_str($_GET["device_vendor_uuid"]);
	}

//get http post variables and set them to php variables
	if (count($_POST)>0) {
		//$label = check_str($_POST["label"]);
		$name = check_str($_POST["name"]);
		$value = check_str($_POST["value"]);
		$enabled = check_str($_POST["enabled"]);
		$description = check_str($_POST["description"]);
	}

//process the http variables
	if (count($_POST) > 0 && strlen($_POST["persistformvar"]) == 0) {

		//get the uuid
			if ($action == "update") {
				$device_vendor_function_uuid = check_str($_POST["device_vendor_function_uuid"]);
			}

		//check for all required data
			$msg = '';
			//if (strlen($label) == 0) { $msg .= $text['message-required']." ".$text['label-label']."<br>\n"; }
			if (strlen($name) == 0) { $msg .= $text['message-required']." ".$text['label-name']."<br>\n"; }
			if (strlen($value) == 0) { $msg .= $text['message-required']." ".$text['label-value']."<br>\n"; }
			if (strlen($enabled) == 0) { $msg .= $text['message-required']." ".$text['label-enabled']."<br>\n"; }
			//if (strlen($description) == 0) { $msg .= $text['message-required']." ".$text['label-description']."<br>\n"; }
			if (strlen($msg) > 0 && strlen($_POST["persistformvar"]) == 0) {
				require_once "resources/header.php";
				require_once "resources/persist_form_var.php";
				echo "<div align='center'>\n";
				echo "<table><tr><td>\n";
				echo $msg."<br />";
				echo "</td></tr></table>\n";
				persistformvar($_POST);
				echo "</div>\n";
				require_once "resources/footer.php";
				return;
			}

		//add or update the database
			if ($_POST["persistformvar"] != "true") {

				//add vendor functions
					if ($action == "add" && permission_exists('device_vendor_function_add')) {
						$device_vendor_function_uuid = uuid();
						$sql = "insert into v_device_vendor_functions ";
						$sql .= "(";
						$sql .= "device_vendor_function_uuid, ";
						$sql .= "device_vendor_uuid, ";
						//$sql .= "label, ";
						$sql .= "name, ";
						$sql .= "value, ";
						$sql .= "enabled, ";
						$sql .= "description ";
						$sql .= ")";
						$sql .= "values ";
						$sql .= "(";
						$sql .= "'".$device_vendor_function_uuid."', ";
						$sql .= "'$device_vendor_uuid', ";
						//$sql .= "'$label', ";
						$sql .= "'$name', ";
						$sql .= "'$value', ";
						$sql .= "'$enabled', ";
						$sql .= "'$description' ";
						$sql .= ")";
						$db->exec(check_sql($sql));
						unset($sql);
					} //if ($action == "add")

				//update vendor functions
					if ($action == "update" && permission_exists('device_vendor_function_edit')) {
						$sql = "update v_device_vendor_functions set ";
						$sql .= "device_vendor_uuid = '$device_vendor_uuid', ";
						//$sql .= "label = '$label', ";
						$sql .= "name = '$name', ";
						$sql .= "value = '$value', ";
						$sql .= "enabled = '$enabled', ";
						$sql .= "description = '$description' ";
						$sql .= "where device_vendor_function_uuid = '$device_vendor_function_uuid'";
						$db->exec(check_sql($sql));
						unset($sql);
					} //if ($action == "update")

				//add a group to the menu
					if (permission_exists('device_vendor_function_add') && $_REQUEST["group_uuid_name"] != '') {

						//get the group uuid and group_name
							$group_data = explode('|', check_str($_REQUEST["group_uuid_name"]));
							$group_uuid = $group_data[0];
							$group_name = $group_data[1];

						//add the group to the menu
							if (strlen($device_vendor_function_uuid) > 0) {
								$device_vendor_function_group_uuid = uuid();
								$sql = "insert into v_device_vendor_function_groups ";
								$sql .= "(";
								$sql .= "device_vendor_function_group_uuid, ";
								$sql .= "device_vendor_function_uuid, ";
								$sql .= "device_vendor_uuid, ";
								$sql .= "group_name, ";
								$sql .= "group_uuid ";
								$sql .= ")";
								$sql .= "values ";
								$sql .= "(";
								$sql .= "'".$device_vendor_function_group_uuid."', ";
								$sql .= "'".$device_vendor_function_uuid."', ";
								$sql .= "'".$device_vendor_uuid."', ";
								$sql .= "'".$group_name."', ";
								$sql .= "'".$group_uuid."' ";
								$sql .= ")";
								$db->exec($sql);
							}
					}

				//redirect the user
					$_SESSION["message"] = $text['message-'.$action];
					header("Location: device_vendor_function_edit.php?id=".escape($device_vendor_function_uuid) ."&device_vendor_uuid=".escape($device_vendor_uuid));
					return;
			} //if ($_POST["persistformvar"] != "true")
	} //(count($_POST)>0 && strlen($_POST["persistformvar"]) == 0)

//pre-populate the form
	if (count($_GET) > 0 && $_POST["persistformvar"] != "true") {
		$device_vendor_function_uuid = check_str($_GET["id"]);
		$sql = "select * from v_device_vendor_functions ";
		$sql .= "where device_vendor_function_uuid = '$device_vendor_function_uuid' ";
		$prep_statement = $db->prepare(check_sql($sql));
		$prep_statement->execute();
		$device_vendor_functions = $prep_statement->fetchAll(PDO::FETCH_NAMED);
		foreach ($device_vendor_functions as &$row) {
			//$label = $row["label"];
			$name = $row["name"];
			$value = $row["value"];
			$enabled = $row["enabled"];
			$description = $row["description"];
		}
		unset ($prep_statement);
	}

//group groups assigned
	$sql = "select ";
	$sql .= "	fg.*, g.domain_uuid as group_domain_uuid ";
	$sql .= "from ";
	$sql .= "	v_device_vendor_function_groups as fg, ";
	$sql .= "	v_groups as g ";
	$sql .= "where ";
	$sql .= "	fg.group_uuid = g.group_uuid ";
	$sql .= "	and fg.device_vendor_uuid = :device_vendor_uuid ";
	//$sql .= "	and fg.device_vendor_uuid = '$device_vendor_uuid' ";
	$sql .= "	and fg.device_vendor_function_uuid = :device_vendor_function_uuid ";
	//$sql .= "	and fg.device_vendor_function_uuid = '$device_vendor_function_uuid' ";
	$sql .= "order by ";
	$sql .= "	g.domain_uuid desc, ";
	$sql .= "	g.group_name asc ";
	$prep_statement = $db->prepare(check_sql($sql));
	$prep_statement->bindParam(':device_vendor_uuid', $device_vendor_uuid);
	$prep_statement->bindParam(':device_vendor_function_uuid', $device_vendor_function_uuid);
	$prep_statement->execute();
	$function_groups = $prep_statement->fetchAll(PDO::FETCH_NAMED);
	unset($sql, $prep_statement);

//set the assigned_groups array
	if (is_array($menu_item_groups)) {
		foreach($menu_item_groups as $field) {
			if (strlen($field['group_name']) > 0) {
				$assigned_groups[] = $field['group_uuid'];
			}
		}
	}

//get the groups
	$sql = "select * from v_groups ";
	if (sizeof($assigned_groups) > 0) {
		$sql .= "where group_uuid not in ('".implode("','",$assigned_groups)."') ";
	}
	$sql .= "order by domain_uuid desc, group_name asc ";
	$prep_statement = $db->prepare(check_sql($sql));
	$prep_statement->execute();
	$groups = $prep_statement->fetchAll(PDO::FETCH_NAMED);
	unset($sql, $prep_statement);

//show the header
	require_once "resources/header.php";

//show the content
	echo "<form name='frm' id='frm' method='post' action=''>\n";
	echo "<table width='100%'  border='0' cellpadding='0' cellspacing='0'>\n";
	echo "<tr>\n";
	echo "<td align='left' width='30%' nowrap='nowrap' valign='top'><b>".$text['title-device_vendor_function']."</b><br><br></td>\n";
	echo "<td width='70%' align='right' valign='top'>\n";
	echo "	<input type='button' class='btn' name='' alt='".$text['button-back']."' onclick=\"window.location='device_vendor_edit.php?id=".escape($device_vendor_uuid)."'\" value='".$text['button-back']."'>";
	echo "	<input type='submit' name='submit' class='btn' value='".$text['button-save']."'>";
	echo "</td>\n";
	echo "</tr>\n";

	//echo "<tr>\n";
	//echo "<td class='vncellreq' valign='top' align='left' nowrap='nowrap'>\n";
	//echo "	".$text['label-label']."\n";
	//echo "</td>\n";
	//echo "<td class='vtable' align='left'>\n";
	//echo "	<input class='formfld' type='text' name='label' maxlength='255' value=\"".escape($label)."\">\n";
	//echo "<br />\n";
	//echo $text['description-label']."\n";
	//echo "</td>\n";
	//echo "</tr>\n";

	echo "<tr>\n";
	echo "<td class='vncellreq' valign='top' align='left' nowrap='nowrap'>\n";
	echo "	".$text['label-name']."\n";
	echo "</td>\n";
	echo "<td class='vtable' align='left'>\n";
	echo "	<input class='formfld' type='text' name='name' maxlength='255' value=\"".escape($name)."\">\n";
	echo "<br />\n";
	echo $text['description-name']."\n";
	echo "</td>\n";
	echo "</tr>\n";

	echo "<tr>\n";
	echo "<td class='vncellreq' valign='top' align='left' nowrap='nowrap'>\n";
	echo "	".$text['label-value']."\n";
	echo "</td>\n";
	echo "<td class='vtable' align='left'>\n";
	echo "	<input class='formfld' type='text' name='value' maxlength='255' value=\"".escape($value)."\">\n";
	echo "<br />\n";
	echo $text['description-value']."\n";
	echo "</td>\n";
	echo "</tr>\n";

	//echo "<pre>\n";
	//print_r($function_groups);
	//echo "</pre>\n";
	echo "	<tr>";
	echo "		<td class='vncell' valign='top'>".$text['label-groups']."</td>";
	echo "		<td class='vtable'>";
	if (is_array($function_groups)) {
		echo "<table cellpadding='0' cellspacing='0' border='0'>\n";
		foreach($function_groups as $field) {
			if (strlen($field['group_name']) > 0) {
				echo "<tr>\n";
				echo "	<td class='vtable' style='white-space: nowrap; padding-right: 30px;' nowrap='nowrap'>";
				echo $field['group_name'].(($field['group_domain_uuid'] != '') ? "@".$_SESSION['domains'][$field['group_domain_uuid']]['domain_name'] : null);
				echo "	</td>\n";
				if (permission_exists('group_member_delete') || if_group("superadmin")) {
					echo "	<td class='list_control_icons' style='width: 25px;'>";
					echo 		"<a href='device_vendor_function_edit.php?id=".$field['device_vendor_function_group_uuid']."&group_uuid=".$field['group_uuid']."&device_vendor_function_uuid=".$device_vendor_function_uuid."&device_vendor_uuid=".$device_vendor_uuid."&a=delete' alt='".$text['button-delete']."' onclick=\"return confirm('".$text['confirm-delete']."')\">".$v_link_label_delete."</a>";
					echo "	</td>";
				}
				echo "</tr>\n";
			}
		}
		echo "</table>\n";
	}
	if (is_array($groups)) {
		echo "<br />\n";
		echo "<select name='group_uuid_name' class='formfld' style='width: auto; margin-right: 3px;'>\n";
		echo "	<option value=''></option>\n";
		foreach($groups as $field) {
			if ($field['group_name'] == "superadmin" && !if_group("superadmin")) { continue; }	//only show the superadmin group to other superadmins
			if ($field['group_name'] == "admin" && (!if_group("superadmin") && !if_group("admin") )) { continue; }	//only show the admin group to other admins
			if (!in_array($field["group_uuid"], $assigned_groups)) {
				echo "	<option value='".escape($field['group_uuid'])."|".escape($field['group_name'])."'>".escape($field['group_name']).(($field['domain_uuid'] != '') ? "@".escape($_SESSION['domains'][$field['domain_uuid']]['domain_name']) : null)."</option>\n";
			}
		}
		echo "</select>";
		echo "<input type='submit' class='btn' name='submit' value=\"".$text['button-add']."\">\n";
	}
	echo "		</td>";
	echo "	</tr>";

	echo "<tr>\n";
	echo "<td class='vncellreq' valign='top' align='left' nowrap='nowrap'>\n";
	echo "	".$text['label-enabled']."\n";
	echo "</td>\n";
	echo "<td class='vtable' align='left'>\n";
	echo "	<select class='formfld' name='enabled'>\n";
	echo "	<option value=''></option>\n";
	if ($enabled == "true") {
		echo "	<option value='true' selected='selected'>".$text['label-true']."</option>\n";
	}
	else {
		echo "	<option value='true'>".$text['label-true']."</option>\n";
	}
	if ($enabled == "false") {
		echo "	<option value='false' selected='selected'>".$text['label-false']."</option>\n";
	}
	else {
		echo "	<option value='false'>".$text['label-false']."</option>\n";
	}
	echo "	</select>\n";
	echo "<br />\n";
	echo $text['description-enabled']."\n";
	echo "</td>\n";
	echo "</tr>\n";

	echo "<tr>\n";
	echo "<td class='vncell' valign='top' align='left' nowrap='nowrap'>\n";
	echo "	".$text['label-description']."\n";
	echo "</td>\n";
	echo "<td class='vtable' align='left'>\n";
	echo "	<input class='formfld' type='text' name='description' maxlength='255' value=\"".escape($description)."\">\n";
	echo "<br />\n";
	echo $text['description-description']."\n";
	echo "</td>\n";
	echo "</tr>\n";
	echo "	<tr>\n";
	echo "		<td colspan='2' align='right'>\n";
	echo "				<input type='hidden' name='device_vendor_uuid' value='".escape($device_vendor_uuid)."'>\n";
	if ($action == "update") {
		echo "				<input type='hidden' name='device_vendor_function_uuid' value='".escape($device_vendor_function_uuid)."'>\n";
	}
	echo "				<input type='submit' name='submit' class='btn' value='".$text['button-save']."'>\n";
	echo "		</td>\n";
	echo "	</tr>";
	echo "</table>";
	echo "</form>";
	echo "<br /><br />";

//include the footer
	require_once "resources/footer.php";

?>
