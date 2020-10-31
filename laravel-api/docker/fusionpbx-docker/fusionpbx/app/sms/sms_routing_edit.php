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
	Portions created by the Initial Developer are Copyright (C) 2008-2018
	the Initial Developer. All Rights Reserved.

	Contributor(s):
	Mark J Crane <markjcrane@fusionpbx.com>
	Igor Olhovskiy <igorolhovskiy@gmail.com>
*/
//includes
	require_once "root.php";
	require_once "resources/require.php";
	require_once "resources/check_auth.php";

//check permissions
	if (permission_exists('sms_routing_add') || permission_exists('sms_routing_edit')) {
		//access granted
	} else {
		echo "access denied";
		exit;
	}

//add multi-lingual support
	$language = new text;
	$text = $language->get();

//action add or update
	if (isset($_REQUEST["id"])) {
		$action = "update";
		$sms_routing_uuid = check_str($_REQUEST["id"]);
	} else {
		$action = "add";
	}

//get http post variables and set them to php variables
	if (count($_POST) > 0) {
		$sms_routing_source = check_str($_POST["sms_routing_source"]);
		$sms_routing_destination = check_str($_POST["sms_routing_destination"]);
		$sms_routing_target_type = check_str($_POST["sms_routing_target_type"]);
		$sms_routing_target_details = check_str($_POST["sms_routing_target_details"]);
		$sms_routing_number_translation_source = check_str($_POST["sms_routing_number_translation_source"]);
		$sms_routing_number_translation_destination = check_str($_POST["sms_routing_number_translation_destination"]);
		$sms_routing_enabled = check_str($_POST["sms_routing_enabled"]);
		$sms_routing_description = check_str($_POST["sms_routing_description"]);
	}

//handle the http post
	if (count($_POST) > 0 && strlen($_POST["persistformvar"]) == 0) {
	
		$msg = '';
	
		//check for all required data
		if (strlen($sms_routing_source) == 0) { 
			$msg .= $text['label-sms_routing_source']."<br>\n"; 
		}
		if (strlen($sms_routing_destination) == 0) {
			$msg .= $text['label-sms_routing_destination']."<br>\n"; 
		}
		if (strlen($sms_routing_target_type) == 0) { 
			$msg .= $text['label-sms_routing_target_type']."<br>\n"; 
		}

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
		if (($_POST["persistformvar"] != "true") > 0) {

			if ($action == "add") {

				$sms_routing_uuid = uuid();

				$sql = "INSERT INTO v_sms_routing";
				$sql .= " (";
				$sql .= "domain_uuid, ";
				$sql .= "sms_routing_uuid, ";
				$sql .= "sms_routing_source, ";
				$sql .= "sms_routing_destination, ";
				$sql .= "sms_routing_target_type, ";
				$sql .= "sms_routing_target_details, ";
				$sql .= "sms_routing_number_translation_source, ";
				$sql .= "sms_routing_number_translation_destination, ";
				$sql .= "sms_routing_enabled, ";
				$sql .= "sms_routing_description ";
				$sql .= ") ";
				$sql .= "VALUES ";
				$sql .= "(";
				$sql .= "'".$_SESSION['domain_uuid']."', ";
				$sql .= "'$sms_routing_uuid', ";
				$sql .= "'$sms_routing_source', ";
				$sql .= "'$sms_routing_destination', ";
				$sql .= "'$sms_routing_target_type', ";
				$sql .= "'$sms_routing_target_details', ";
				$sql .= "'$sms_routing_number_translation_source', ";
				$sql .= "'$sms_routing_number_translation_destination', ";
				$sql .= "'$sms_routing_enabled', ";
				$sql .= "'$sms_routing_description'";
				$sql .= ")";

				$prep_statement = $db->prepare(check_sql($sql));
				if ($prep_statement) {
					$prep_statement->execute();
				}
				unset($sql);

				messages::add($text['label-add-complete']);
				header("Location: sms_routing.php");
				return;
			} //if ($action == "add")

			if ($action == "update") {

				$sql = "UPDATE v_sms_routing SET ";
				$sql .= "sms_routing_source = '$sms_routing_source', ";
				$sql .= "sms_routing_destination = '$sms_routing_destination', ";
				$sql .= "sms_routing_target_type = '$sms_routing_target_type', ";
				$sql .= "sms_routing_target_details = '$sms_routing_target_details', ";
				$sql .= "sms_routing_number_translation_source = '$sms_routing_number_translation_source', ";
				$sql .= "sms_routing_number_translation_destination = '$sms_routing_number_translation_destination', ";
				$sql .= "sms_routing_enabled = '$sms_routing_enabled', ";
				$sql .= "sms_routing_description = '$sms_routing_description' ";
				$sql .= "WHERE domain_uuid = '".$_SESSION['domain_uuid']."' ";
				$sql .= "AND sms_routing_uuid = '$sms_routing_uuid'";

				$prep_statement = $db->prepare(check_sql($sql));
				if ($prep_statement) {
					$prep_statement->execute();
				}
				unset($sql);

				messages::add($text['label-update-complete']);
				header("Location: sms_routing.php");
				return;
			} //if ($action == "update")
		} //if ($_POST["persistformvar"] != "true")
	} //(count($_POST)>0 && strlen($_POST["persistformvar"]) == 0)

//pre-populate the form
	if (count($_GET)>0 && $_POST["persistformvar"] != "true") {
		$sms_routing_uuid = $_GET["id"];
		$sql = "SELECT * FROM v_sms_routing ";
		$sql .= "WHERE domain_uuid = '".$_SESSION['domain_uuid']."' ";
		$sql .= "AND sms_routing_uuid = '$sms_routing_uuid' LIMIT 1";
		$prep_statement = $db->prepare(check_sql($sql));
		$result = $prep_statement->execute() ? $prep_statement->fetchAll() : null;

		foreach ($result as &$row) {
			$sms_routing_source = check_str($row["sms_routing_source"]);
			$sms_routing_destination = check_str($row["sms_routing_destination"]);
			$sms_routing_target_type = check_str($row["sms_routing_target_type"]);
			$sms_routing_target_details = check_str($row["sms_routing_target_details"]);
			$sms_routing_number_translation_source = check_str($row["sms_routing_number_translation_source"]);
			$sms_routing_number_translation_destination = check_str($row["sms_routing_number_translation_destination"]);
			$sms_routing_enabled = check_str($row["sms_routing_enabled"]);
			$sms_routing_description = check_str($row["sms_routing_description"]);
		}
		unset ($prep_statement, $sql);
	}

// Get number translation list

	$sql = "SELECT number_translation_name FROM v_number_translations";
	$sql .= " WHERE number_translation_enabled = 'true'";
	
	$prep_statement = $db->prepare(check_sql($sql));
	$number_translation_name_list = $prep_statement->execute() ? $prep_statement->fetchAll() : null;


//show the header
	require_once "resources/header.php";

//show the content

	echo "<form method='post' name='frm' action=''>\n";
	echo "<table width='100%' border='0' cellpadding='0' cellspacing='0'>\n";
	echo "<tr>\n";
	if ($action == "add") {
		echo "<td align='left' width='30%' nowrap='nowrap'><b>".$text['label-edit-add']."</b></td>\n";
	}
	if ($action == "update") {
		echo "<td align='left' width='30%' nowrap='nowrap'><b>".$text['label-edit-edit']."</b></td>\n";
	}
	echo "<td width='70%' align='right'>";
	echo "	<input type='button' class='btn' name='' alt='".$text['button-back']."' onclick=\"window.location='sms_routing.php'\" value='".$text['button-back']."'>";
	echo "	<input type='submit' name='submit' class='btn' value='".$text['button-save']."'>\n";
	echo "</td>\n";
	echo "</tr>\n";
	echo "<tr>\n";
	echo "<td align='left' colspan='2'>\n";
	if ($action == "add") {
		echo $text['label-add-note']."<br /><br />\n";
	}
	if ($action == "update") {
		echo $text['label-edit-note']."<br /><br />\n";
	}
	echo "</td>\n";
	echo "</tr>\n";

	// Source
	echo "<tr>\n";
	echo "<td class='vncellreq' valign='top' align='left' nowrap='nowrap'>\n";
	echo "	".$text['label-sms_routing_source']."\n";
	echo "</td>\n";
	echo "<td class='vtable' align='left'>\n";
	echo "	<input class='formfld' type='text' name='sms_routing_source' maxlength='255' value=\"".escape($sms_routing_source)."\" required='required'>\n";
	echo "<br />\n";
	echo $text['description-sms_routing_source']."\n";
	echo "<br />\n";
	echo "</td>\n";
	echo "</tr>\n";

	// Destination
	echo "<tr>\n";
	echo "<td class='vncellreq' valign='top' align='left' nowrap='nowrap'>\n";
	echo "	".$text['label-sms_routing_destination']."\n";
	echo "</td>\n";
	echo "<td class='vtable' align='left'>\n";
	echo "	<input class='formfld' type='text' name='sms_routing_destination' maxlength='255' value=\"".escape($sms_routing_destination)."\" required='required'>\n";
	echo "<br />\n";
	echo $text['description-sms_routing_destination']."\n";
	echo "</td>\n";
	echo "</tr>\n";

	// Target Type
	echo "<tr>\n";
	echo "<td class='vncellreq' valign='top' align='left' nowrap='nowrap'>\n";
	echo "	".$text['label-sms_routing_target_type']."\n";
	echo "</td>\n";
	echo "<td class='vtable' align='left'>\n";
	echo "<select name='sms_routing_target_type' class='formfld' required='required'>\n";
	if ($sms_routing_target_type == 'carrier') {
		echo "<option value='carrier' selected>" . $text['label-sms_routing_target_type_carrier'] . "</option>\n";
		echo "<option value='internal'>" . $text['label-sms_routing_target_type_internal'] . "</option>\n";
	} else {
		echo "<option value='carrier'>" . $text['label-sms_routing_target_type_carrier'] . "</option>\n";
		echo "<option value='internal' selected>" . $text['label-sms_routing_target_type_internal'] . "</option>\n";
	}
	echo "		</select>\n";
	echo "<br />\n";
	echo $text['description-sms_routing_target_type']."\n";
	echo "</td>\n";
	echo "</tr>\n";

	// Target Details
	echo "<tr>\n";
	echo "<td class='vncellreq' valign='top' align='left' nowrap='nowrap'>\n";
	echo "	".$text['label-sms_routing_target_details']."\n";
	echo "</td>\n";
	echo "<td class='vtable' align='left'>\n";
	echo "	<input class='formfld' type='text' name='sms_routing_target_details' maxlength='255' value=\"".escape($sms_routing_target_details)."\" required='required'>\n";
	echo "<br />\n";
	echo $text['description-sms_routing_target_details']."\n";
	echo "</td>\n";
	echo "</tr>\n";

	// Number translation source
	echo "<tr>\n";
	echo "<td class='vncell' valign='top' align='left' nowrap='nowrap'>\n";
	echo "	".$text['label-sms_routing_number_translation_source']."\n";
	echo "</td>\n";
	echo "<td class='vtable' align='left'>\n";
	echo "	<select class='formfld' name='sms_routing_number_translation_source'>\n";
	echo "<option value=''> </option>\n";
	foreach ($number_translation_name_list as $number_translation) {

		$number_translation_name = $number_translation['number_translation_name'];
		$selected = $sms_routing_number_translation_source == $number_translation_name ? " selected" : "";
		echo "<option value='" . $number_translation_name . "'" . $selected . "> " . $number_translation_name . "</option>\n";
	}
	echo "	</select>\n";
	echo "<br />\n";
	echo $text['description-sms_routing_number_translation_source']."\n";
	echo "\n";
	echo "</td>\n";
	echo "</tr>\n";

	// Number translation destination
	echo "<tr>\n";
	echo "<td class='vncell' valign='top' align='left' nowrap='nowrap'>\n";
	echo "	".$text['label-sms_routing_number_translation_destination']."\n";
	echo "</td>\n";
	echo "<td class='vtable' align='left'>\n";
	echo "	<select class='formfld' name='sms_routing_number_translation_destination'>\n";
	echo "<option value=''> </option>\n";
	foreach ($number_translation_name_list as $number_translation) {

		$number_translation_name = $number_translation['number_translation_name'];
		$selected = $sms_routing_number_translation_destination == $number_translation_name ? " selected" : "";
		echo "<option value='" . $number_translation_name . "'" . $selected . "> " . $number_translation_name . "</option>\n";
	}
	echo "	</select>\n";
	echo "<br />\n";
	echo $text['description-sms_routing_number_translation_destination']."\n";
	echo "\n";
	echo "</td>\n";
	echo "</tr>\n";

	// Show enabled
	echo "<tr>\n";
	echo "<td class='vncell' valign='top' align='left' nowrap='nowrap'>\n";
	echo "	".$text['label-sms_routing_enabled']."\n";
	echo "</td>\n";
	echo "<td class='vtable' align='left'>\n";
	echo "	<select class='formfld' name='sms_routing_enabled'>\n";
	echo "		<option value='true' ".(($sms_routing_enabled == "true") ? "selected" : null).">".$text['label-true']."</option>\n";
	echo "		<option value='false' ".(($sms_routing_enabled == "false") ? "selected" : null).">".$text['label-false']."</option>\n";
	echo "	</select>\n";
	echo "<br />\n";
	echo $text['description-sms_routing_enabled']."\n";
	echo "\n";
	echo "</td>\n";
	echo "</tr>\n";

	// Show description
	echo "<tr>\n";
	echo "<td class='vncellreq' valign='top' align='left' nowrap='nowrap'>\n";
	echo "	".$text['label-sms_routing_description']."\n";
	echo "</td>\n";
	echo "<td class='vtable' align='left'>\n";
	echo "	<input class='formfld' type='text' name='sms_routing_description' maxlength='255' value=\"".escape($sms_routing_description)."\">\n";
	echo "<br />\n";
	echo $text['description-sms_routing_description']."\n";
	echo "</td>\n";
	echo "</tr>\n";


	echo "	<tr>\n";
	echo "		<td colspan='2' align='right'>\n";
	if ($action == "update") {
		echo "		<input type='hidden' name='id' value='".escape($sms_routing_uuid)."'>\n";
	}
	echo "			<br>";
	echo "			<input type='submit' name='submit' class='btn' value='".$text['button-save']."'>\n";
	echo "		</td>\n";
	echo "	</tr>";
	echo "</table>";
	echo "</form>";

	echo "<table>";
	echo "<tr>";
	echo "<td>";
	echo $text['description-sms_routing_edit'];
	echo "</td>";
	echo "</tr>";
	echo "</table>";

//include the footer
	require_once "resources/footer.php";
?>
