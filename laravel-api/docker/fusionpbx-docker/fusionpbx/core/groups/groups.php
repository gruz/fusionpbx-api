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
	Portions created by the Initial Developer are Copyright (C) 2008-2017
	the Initial Developer. All Rights Reserved.

	Contributor(s):
	Mark J Crane <markjcrane@fusionpbx.com>
*/

//includes
	include "root.php";
	require_once "resources/require.php";
	require_once "resources/check_auth.php";

//check the permissions
	if (if_group("admin") || if_group("superadmin")) {
		//access allowed
	}
	else {
		echo "access denied";
		return;
	}

//add multi-lingual support
	$language = new text;
	$text = $language->get();

//show the header
	require_once "resources/header.php";
	$document['title'] = $text['title-group_manager'];
	if (isset($_REQUEST["change"])) {
		//get the values from the HTTP POST and save them as PHP variables
		$change = check_str($_REQUEST["change"]);
		$group_uuid = check_str($_REQUEST["group_uuid"]);
		$group_name = check_str($_REQUEST["group_name"]);

		$sql = "update v_groups set group_protected = '".$change."' ";
		$sql .= "where group_uuid = '".$group_uuid."' ";
		if (!permission_exists('group_domain')) {
			$sql .= "and (";
			$sql .= "	domain_uuid = '".$domain_uuid."' ";
			$sql .= "	or domain_uuid is null ";
			$sql .= ") ";
		}
		$db->exec(check_sql($sql));
		unset($sql);

		messages::add($text['message-update']);
	}

//get the groups
	$sql = "select * from v_groups ";
	if (!(permission_exists('group_all') && $_GET['show'] == 'all')) {
		$sql .= "where domain_uuid = '".$domain_uuid."' ";
		$sql .= "or domain_uuid is null ";
	}
	$sql .= "order by domain_uuid desc, group_name asc ";
	$prep_statement = $db->prepare(check_sql($sql));
	$prep_statement->execute();
	$groups = $prep_statement->fetchAll(PDO::FETCH_NAMED);
	unset($sql, $prep_statement);
	//$system_groups = array('superadmin','admin','user','public','agent');
	$system_groups = array();


//get group counts
	$sql = "select group_uuid, count(user_uuid) as group_count from v_group_users ";
	if (!permission_exists('user_all')) {
		$sql .= "where domain_uuid = '".$_SESSION['domain_uuid']."' ";
	}
	$sql .= "group by group_uuid ";
	$prep_statement = $db->prepare(check_sql($sql));
	$prep_statement->execute();
	$result = $prep_statement->fetchAll(PDO::FETCH_NAMED);
	foreach ($result as $row) {
		$group_counts[$row['group_uuid']] = $row['group_count'];
	}
	unset($sql, $prep_statement, $result, $row);

//show the content
	echo "<table width='100%' cellpadding='0' cellspacing='0' border='0'>";
	echo "<tr>";
	echo "<td width='50%' valign='top'>";
	echo "	<b>".$text['header-group_manager']."</b>";
	echo "	<br><br>";
	echo "</td>";
	echo "<td width='50%' align='right' valign='top'>";
	if (permission_exists('group_all')) {
		if ($_GET['show'] != 'all') {
			echo "<input type='button' class='btn' value='".$text['button-show_all']."' onclick=\"window.location='groups.php?show=all';\">\n";
		}
	}
	if (permission_exists('user_view')) {
		echo "  <input type='button' class='btn' onclick=\"window.location='../users/users.php'\" value='".$text['header-user_manager']."'>";
	}
	if (permission_exists('group_edit')) {
		echo "	<input type='button' class='btn' alt='".$text['button-restore']."' onclick=\"window.location='permissions_default.php'\" value='".$text['button-restore']."'>";
	}
	echo "</td>\n";
	echo "</tr>";
	echo "</table>";
	echo "<br>";

	$c = 0;
	$row_style["0"] = "row_style0";
	$row_style["1"] = "row_style1";

	$echo = "<table class='tr_hover' width='100%' border='0' cellpadding='0' cellspacing='0'>\n";
	$echo .= "<tr>\n";
	if (permission_exists('group_all') && $_GET['show'] == 'all') {
		$echo .= "	<th nowrap='nowrap'>".$text['label-domain']."</th>\n";
	}
	$echo .= "	<th nowrap='nowrap'>".$text['label-group_name']."</th>\n";
	$echo .= "	<th nowrap='nowrap'>".$text['label-group_tools']."</th>\n";
	$echo .= "	<th style='text-align: center;' nowrap='nowrap'>".$text['label-group_protected']."</th>\n";
	$echo .= "	<th nowrap='nowrap'>".$text['label-group_description']."</th>\n";
	$echo .= "	<td class='list_control_icons' style='width: 25px;'>";
	if (permission_exists('group_add')) {
		$echo .= "<a href='groupadd.php' alt='".$text['button-add']."'>".$v_link_label_add."</a>";
	}
	$echo .= "	</td>\n";
	$echo .= "</tr>\n";

	$count = 0;
	foreach ($groups as &$row) {
		$domain_uuid = $row['domain_uuid'];
		$group_uuid = $row["group_uuid"];
		$group_name = $row["group_name"];
		$group_protected = $row["group_protected"];
		$group_description = $row["group_description"];
		if (strlen($group_name) == 0) { $group_name = "&nbsp;"; }
		if (strlen($group_description) == 0) { $group_description = "&nbsp;"; }
		$group_description = wordwrap($group_description, 50, "<br />\n");

		if (!if_group("superadmin") && $group_name == "superadmin") {
			//hide the superadmin group from non superadmin's
		}
		else {
			if (permission_exists('group_edit') && !($domain_uuid == '' && in_array($group_name, $system_groups))) {
				$tr_link = (permission_exists('group_edit')) ? "href='groupedit.php?id=".$group_uuid."'" : null;
			}
			else {
				unset($tr_link);
			}
			$echo .= "<tr ".$tr_link.">\n";
			if (permission_exists('group_all') && $_GET['show'] == 'all') {
				$echo .= "";
				if (strlen($_SESSION['domains'][$domain_uuid]['domain_name']) > 0) {
					$domain = $_SESSION['domains'][$domain_uuid]['domain_name'];
				}
				else {
					$domain = $text['label-global'];
				}
				$echo .= "<td class='".$row_style[$c]."' nowrap='nowrap'>$domain</td>\n";
			}
			$echo .= "<td class='".$row_style[$c]."' nowrap='nowrap'>";
			if (permission_exists('group_edit') && !($domain_uuid == '' && in_array($group_name, $system_groups))) {
				$echo .= "<a href='groupedit.php?id=".$group_uuid."'>".(($domain_uuid == '' && $_GET['show'] != 'all') ? "<i>".$group_name."</i>" : $group_name)."</a>";
			}
			else {
				$echo .= ($domain_uuid == '' && $_GET['show'] != 'all') ? "<i>".$group_name."</i>" : $group_name;
			}
			$echo .= "</td>\n";
			$echo .= "<td class='".$row_style[$c]." tr_link_void' nowrap='nowrap'>\n";
			if (permission_exists('group_add') || if_group("superadmin")) {
				$echo .= "<a class='' href='group_permissions.php?group_uuid=".$group_uuid."' title='".$text['label-group_permissions']."'>".$text['label-group_permissions']."</a>&nbsp;&nbsp;&nbsp;";
			}
			if (permission_exists('group_member_view') || if_group("superadmin")) {
				$echo .= "<a class='' href='groupmembers.php?group_uuid=".$group_uuid."&group_name=".$group_name."' title='".$text['label-group_members']."'>".$text['label-group_members']."</a>";
				if (sizeof($group_counts) > 0 && $group_counts[$group_uuid] > 0) {
					$echo .= " <span style='font-size: 80%;'>(".$group_counts[$group_uuid].")</span>";
				}
			}
			$echo .= "</td>\n";
			$echo .= "<td class='".$row_style[$c]." tr_link_void' style='padding: 0px; text-align: center;' align='center' nowrap='nowrap'>\n";
			$echo .= "	<input type='checkbox' name='group_protected' ".(($group_protected == "true") ? "checked='checked'" : null)." value='".(($group_protected == "true") ? 'false' : 'true')."' onchange=\"window.location='".PROJECT_PATH."/core/groups/groups.php?change=".(($group_protected == "true") ? 'false' : 'true')."&group_uuid=".$group_uuid."&group_name=".$group_name.(($_GET['show'] == 'all') ? "&show=all" : null)."';\">\n";
			$echo .= "</td>\n";
			$echo .= "<td class='row_stylebg' nowrap='nowrap'>".$group_description."</td>\n";
			$echo .= "<td class='list_control_icons' style='width: 25px;'>";
			if (permission_exists('group_edit')) {
				if (!($domain_uuid == '' && in_array($group_name, $system_groups))) {
					$echo .= "<a href='groupedit.php?id=".$group_uuid."' alt='".$text['button-edit']."'>".$v_link_label_edit."</a>";
				}
				else {
					$echo .= "<span onclick=\"alert('".$text['message-default_system_group']."');\" alt='".$text['button-edit']."'>".str_replace("list_control_icon", "list_control_icon_disabled", $v_link_label_edit)."</span>";
				}
			}
			if (permission_exists('group_delete')) {
				if (!($domain_uuid == '' && in_array($group_name, $system_groups))) {
					$echo .= "<a href='groupdelete.php?id=".$group_uuid."' onclick=\"return confirm('".$text['confirm-delete']."')\" alt='".$text['button-delete']."'>".$v_link_label_delete."</a>";
				}
				else {
					$echo .= "<span onclick=\"alert('".$text['message-default_system_group']."');\" alt='".$text['button-delete']."'>".str_replace("list_control_icon", "list_control_icon_disabled", $v_link_label_delete)."</span>";
				}
			}
			$echo .= "</td>\n";
			$echo .= "</tr>\n";
		}
		$c = ($c) ? 0 : 1;
		$count++;
	}

	$echo .= "<tr>\n";
	$echo .= "<td colspan='".((permission_exists('group_all') && $_GET['show'] == 'all') ? 5 : 4)."'>&nbsp;</td>";
	$echo .= "<td class='list_control_icons' style='width: 25px;'>";
	if (permission_exists('group_add')) {
		$echo .= "<a href='groupadd.php' alt='".$text['button-add']."'>".$v_link_label_add."</a>";
	}
	$echo .= "</td>\n";
	$echo .= "</tr>\n";

	$echo .= "</table>\n";
	$echo .= "<br>";

	if ($count > 0) {
		echo $echo;
	}

//show the footer
	require_once "resources/footer.php";

?>
