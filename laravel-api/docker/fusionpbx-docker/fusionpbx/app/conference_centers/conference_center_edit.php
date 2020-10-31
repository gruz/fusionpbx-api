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
*/

//includes
	require_once "root.php";
	require_once "resources/require.php";

//check permissions
	require_once "resources/check_auth.php";
	if (permission_exists('conference_center_add') || permission_exists('conference_center_edit')) {
		//access granted
	}
	else {
		echo "access denied";
		exit;
	}

//add multi-lingual support
	$language = new text;
	$text = $language->get();

//action add or update
	if (isset($_REQUEST["id"])) {
		$action = "update";
		$conference_center_uuid = check_str($_REQUEST["id"]);
	}
	else {
		$action = "add";
	}

//get http post variables and set them to php variables
	if (is_array($_POST)) {
		$conference_center_uuid = check_str($_POST["conference_center_uuid"]);
		$dialplan_uuid = check_str($_POST["dialplan_uuid"]);
		$conference_center_name = check_str($_POST["conference_center_name"]);
		$conference_center_extension = check_str($_POST["conference_center_extension"]);
		$conference_center_greeting = check_str($_POST["conference_center_greeting"]);
		$conference_center_pin_length = check_str($_POST["conference_center_pin_length"]);
		$conference_center_enabled = check_str($_POST["conference_center_enabled"]);
		$conference_center_description = check_str($_POST["conference_center_description"]);
	}

//process the user data and save it to the database
	if (count($_POST) > 0 && strlen($_POST["persistformvar"]) == 0) {

		//get the uuid from the POST
			if ($action == "update") {
				$conference_center_uuid = check_str($_POST["conference_center_uuid"]);
			}

		//check for all required data
			$msg = '';
			//if (strlen($dialplan_uuid) == 0) { $msg .= "Please provide: Dialplan UUID<br>\n"; }
			if (strlen($conference_center_name) == 0) { $msg .= "Please provide: Name<br>\n"; }
			if (strlen($conference_center_extension) == 0) { $msg .= "Please provide: Extension<br>\n"; }
			if (strlen($conference_center_pin_length) == 0) { $msg .= "Please provide: PIN Length<br>\n"; }
			//if (strlen($conference_center_order) == 0) { $msg .= "Please provide: Order<br>\n"; }
			//if (strlen($conference_center_description) == 0) { $msg .= "Please provide: Description<br>\n"; }
			if (strlen($conference_center_enabled) == 0) { $msg .= "Please provide: Enabled<br>\n"; }
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

		//set the domain_uuid
			$_POST["domain_uuid"] = $_SESSION["domain_uuid"];

		//add the conference_center_uuid
			if (!isset($_POST["conference_center_uuid"])) {
				$conference_center_uuid = uuid();
				$_POST["conference_center_uuid"] = $conference_center_uuid;
			}

		//add the dialplan_uuid
			if (!isset($_POST["dialplan_uuid"])) {
				$dialplan_uuid = uuid();
				$_POST["dialplan_uuid"] = $dialplan_uuid;
			}

		//build the xml dialplan
			$dialplan_xml = "<extension name=\"".$conference_center_name."\" continue=\"\" uuid=\"".$dialplan_uuid."\">\n";
			if ($conference_center_pin_length > 1 && $conference_center_pin_length < 4) {
				$dialplan_xml .= "	<condition field=\"destination_number\" expression=\"^(".$conference_center_extension.")(\d{".$conference_center_pin_length."})$\" break=\"on-true\">\n";
				$dialplan_xml .= "		<action application=\"set\" data=\"destination_number=$1\"/>\n";
				$dialplan_xml .= "		<action application=\"set\" data=\"pin_number=$2\"/>\n";
				$dialplan_xml .= "		<action application=\"lua\" data=\"app.lua conference_center\"/>\n";
				$dialplan_xml .= "	</condition>\n";
			}
			$dialplan_xml .= "	<condition field=\"destination_number\" expression=\"^".$conference_center_extension."$\">\n";
			$dialplan_xml .= "		<action application=\"lua\" data=\"app.lua conference_center\"/>\n";
			$dialplan_xml .= "	</condition>\n";
			$dialplan_xml .= "</extension>\n";

		//build the dialplan array
			$dialplan["domain_uuid"] = $_SESSION['domain_uuid'];
			$dialplan["dialplan_uuid"] = $dialplan_uuid;
			$dialplan["dialplan_name"] = $conference_center_name;
			$dialplan["dialplan_number"] = $conference_center_extension;
			$dialplan["dialplan_context"] = $_SESSION['context'];
			$dialplan["dialplan_continue"] = "false";
			$dialplan["dialplan_xml"] = $dialplan_xml;
			$dialplan["dialplan_order"] = "333";
			$dialplan["dialplan_enabled"] = $conference_center_enabled;
			$dialplan["dialplan_description"] = $conference_center_description;
			$dialplan["app_uuid"] = "b81412e8-7253-91f4-e48e-42fc2c9a38d9";

		//prepare the array
			$array['conference_centers'][] = $_POST;
			$array['dialplans'][] = $dialplan;

		//add the dialplan permission
			$p = new permissions;
			$p->add("dialplan_add", "temp");
			$p->add("dialplan_edit", "temp");

		//save to the data
			$database = new database;
			$database->app_name = "conference_centers";
			$database->app_uuid = "b81412e8-7253-91f4-e48e-42fc2c9a38d9";
			if (strlen($conference_center_uuid) > 0) {
				$database->uuid($conference_center_uuid);
			}
			$database->save($array);
			$message = $database->message;

		//remove the temporary permission
			$p->delete("dialplan_add", "temp");
			$p->delete("dialplan_edit", "temp");

		//debug information
			//echo "<pre>\n";
			//print_r($message);
			//echo "</pre>\n";
			//exit;

		//syncrhonize configuration
			save_dialplan_xml();

		//apply settings reminder
			$_SESSION["reload_xml"] = true;

		//clear the cache
			$cache = new cache;
			$cache->delete("dialplan:".$_SESSION["context"]);

		//redirect the user
			if (isset($action)) {
				if ($action == "add") {
					messages::add($text['message-add']);
				}
				if ($action == "update") {
					messages::add($text['message-update']);
				}
				header("Location: conference_centers.php");
				return;
			}
	} //(is_array($_POST) && strlen($_POST["persistformvar"]) == 0)

//pre-populate the form
	if (is_array($_GET) && $_POST["persistformvar"] != "true") {
		$conference_center_uuid = check_str($_GET["id"]);
		$sql = "select * from v_conference_centers ";
		$sql .= "where domain_uuid = '$domain_uuid' ";
		$sql .= "and conference_center_uuid = '$conference_center_uuid' ";
		$prep_statement = $db->prepare(check_sql($sql));
		$prep_statement->execute();
		$result = $prep_statement->fetchAll(PDO::FETCH_NAMED);
		foreach ($result as &$row) {
			$conference_center_uuid = $row["conference_center_uuid"];
			$dialplan_uuid = $row["dialplan_uuid"];
			$conference_center_name = $row["conference_center_name"];
			$conference_center_extension = $row["conference_center_extension"];
			$conference_center_greeting = $row["conference_center_greeting"];
			$conference_center_pin_length = $row["conference_center_pin_length"];
			$conference_center_enabled = $row["conference_center_enabled"];
			$conference_center_description = $row["conference_center_description"];
		}
		unset ($prep_statement);
	}

//set defaults
	if (strlen($conference_center_enabled) == 0) { $conference_center_enabled = "true"; }
	if (strlen($conference_center_pin_length) == 0) { $conference_center_pin_length = 9; }

//get the recordings
	$sql = "select recording_name, recording_filename from v_recordings ";
	$sql .= "where domain_uuid = '".$_SESSION["domain_uuid"]."' ";
	$sql .= "order by recording_name asc ";
	$prep_statement = $db->prepare(check_sql($sql));
	$prep_statement->execute();
	$recordings = $prep_statement->fetchAll(PDO::FETCH_ASSOC);

//get the phrases
	$sql = "select * from v_phrases ";
	$sql .= "where (domain_uuid = '".$_SESSION["domain_uuid"]."' or domain_uuid is null) ";
	$prep_statement = $db->prepare(check_sql($sql));
	$prep_statement->execute();
	$phrases = $prep_statement->fetchAll(PDO::FETCH_NAMED);

//get the streams
	$sql = "select * from v_streams ";
	$sql .= "where (domain_uuid = '".$_SESSION["domain_uuid"]."' or domain_uuid is null) ";
	$sql .= "and stream_enabled = 'true' ";
	$sql .= "order by stream_name asc ";
	$prep_statement = $db->prepare(check_sql($sql));
	$prep_statement->execute();
	$streams = $prep_statement->fetchAll(PDO::FETCH_NAMED);

//show the header
	require_once "resources/header.php";

//show the content
	echo "<form name='frm' id='frm' method='post' action=''>\n";
	echo "<table width='100%'  border='0' cellpadding='0' cellspacing='0'>\n";
	echo "<tr>\n";
	echo "<td align='left' width='30%' nowrap='nowrap' valign='top'><b>".$text['title-conference_center']."</b><br><br></td>\n";
	echo "<td width='70%' align='right' valign='top'>\n";
	echo "	<input type='button' class='btn' name='' alt='".$text['button-back']."' onclick=\"window.location='conference_centers.php'\" value='".$text['button-back']."'>";
	echo "	<input type='submit' class='btn' value='".$text['button-save']."'>";
	echo "</td>\n";
	echo "</tr>\n";

	echo "<tr>\n";
	echo "<td class='vncellreq' valign='top' align='left' nowrap='nowrap'>\n";
	echo "	".$text['label-conference_center_name']."\n";
	echo "</td>\n";
	echo "<td class='vtable' align='left'>\n";
	echo "	<input class='formfld' type='text' name='conference_center_name' maxlength='255' value=\"".escape($conference_center_name)."\">\n";
	echo "<br />\n";
	echo $text['description-conference_center_name']."\n";
	echo "</td>\n";
	echo "</tr>\n";

	echo "<tr>\n";
	echo "<td class='vncellreq' valign='top' align='left' nowrap='nowrap'>\n";
	echo "	".$text['label-conference_center_extension']."\n";
	echo "</td>\n";
	echo "<td class='vtable' align='left'>\n";
	echo "	<input class='formfld' type='text' name='conference_center_extension' maxlength='255' value=\"".escape($conference_center_extension)."\">\n";
	echo "<br />\n";
	echo $text['description-conference_center_extension']."\n";
	echo "</td>\n";
	echo "</tr>\n";

	echo "<tr>\n";
	echo "<td class='vncell' valign='top' align='left' nowrap='nowrap'>\n";
	echo "	".$text['label-conference_center_greeting']."\n";
	echo "</td>\n";
	echo "<td class='vtable' align='left'>\n";
	//echo "	<input class='formfld' type='text' name='conference_center_greeting' maxlength='255' value=\"".escape($conference_center_greeting)."\">\n";
	if (permission_exists('conference_center_add') || permission_exists('conference_center_edit')) {
		echo "<script>\n";
		echo "var Objs;\n";
		echo "\n";
		echo "function changeToInput(obj){\n";
		echo "	tb=document.createElement('INPUT');\n";
		echo "	tb.type='text';\n";
		echo "	tb.name=obj.name;\n";
		echo "	tb.setAttribute('class', 'formfld');\n";
		echo "	tb.setAttribute('style', 'width: 350px;');\n";
		echo "	tb.value=obj.options[obj.selectedIndex].value;\n";
		echo "	tbb=document.createElement('INPUT');\n";
		echo "	tbb.setAttribute('class', 'btn');\n";
		echo "	tbb.setAttribute('style', 'margin-left: 4px;');\n";
		echo "	tbb.type='button';\n";
		echo "	tbb.value=$('<div />').html('&#9665;').text();\n";
		echo "	tbb.objs=[obj,tb,tbb];\n";
		echo "	tbb.onclick=function(){ Replace(this.objs); }\n";
		echo "	obj.parentNode.insertBefore(tb,obj);\n";
		echo "	obj.parentNode.insertBefore(tbb,obj);\n";
		echo "	obj.parentNode.removeChild(obj);\n";
		echo "}\n";
		echo "\n";
		echo "function Replace(obj){\n";
		echo "	obj[2].parentNode.insertBefore(obj[0],obj[2]);\n";
		echo "	obj[0].parentNode.removeChild(obj[1]);\n";
		echo "	obj[0].parentNode.removeChild(obj[2]);\n";
		echo "}\n";
		echo "</script>\n";
		echo "\n";
	}
	echo "	<select name='conference_center_greeting' class='formfld' ".((permission_exists('conference_center_add') || permission_exists('conference_center_edit')) ? "onchange='changeToInput(this);'" : null).">\n";
	echo "		<option></option>\n";
	//recordings
		$tmp_selected = false;
		if (is_array($recordings)) {
			echo "<optgroup label='".$text['label-recordings']."'>\n";
			foreach ($recordings as &$row) {
				$recording_name = $row["recording_name"];
				$recording_filename = $row["recording_filename"];
				$recording_path = $_SESSION['switch']['recordings']['dir']."/".$_SESSION['domain_name'];
				$selected = '';
				if ($conference_center_greeting == $recording_path."/".$recording_filename) {
					$selected = "selected='selected'";
				}
				echo "	<option value='".escape($recording_path)."/".escape($recording_filename)."' ".escape($selected).">".escape($recording_name)."</option>\n";
				unset($selected);
			}
			echo "</optgroup>\n";
		}
	//phrases
		if (count($phrases) > 0) {
			echo "<optgroup label='".$text['label-phrases']."'>\n";
			foreach ($phrases as &$row) {
				$selected = ($conference_center_greeting == "phrase:".$row["phrase_uuid"]) ? true : false;
				echo "	<option value='phrase:".escape($row["phrase_uuid"])."' ".(($selected) ? "selected='selected'" : null).">".escape($row["phrase_name"])."</option>\n";
				if ($selected) { $tmp_selected = true; }
			}
			unset ($prep_statement);
			echo "</optgroup>\n";
		}
	//sounds
		$file = new file;
		$sound_files = $file->sounds();
		if (is_array($sound_files)) {
			echo "<optgroup label='".$text['label-sounds']."'>\n";
			foreach ($sound_files as $key => $value) {
				if (strlen($value) > 0) {
					if (substr($conference_center_greeting, 0, 71) == "\$\${sounds_dir}/\${default_language}/\${default_dialect}/\${default_voice}/") {
						$conference_center_greeting = substr($conference_center_greeting, 71);
					}
					$selected = ($conference_center_greeting == $value) ? true : false;
					echo "	<option value='".escape($value)."' ".(($selected) ? "selected='selected'" : null).">".escape($value)."</option>\n";
					if ($selected) { $tmp_selected = true; }
				}
			}
			echo "</optgroup>\n";
		}
	//select
		if (strlen($conference_center_greeting) > 0) {
			if (permission_exists('conference_center_add') || permission_exists('conference_center_edit')) {
				if (!$tmp_selected) {
					echo "<optgroup label='selected'>\n";
					if (file_exists($_SESSION['switch']['recordings']['dir']."/".$_SESSION['domain_name']."/".$conference_center_greeting)) {
						echo "		<option value='".$_SESSION['switch']['recordings']['dir']."/".$_SESSION['domain_name']."/".escape($conference_center_greeting)."' selected='selected'>".escape($ivr_menu_greet_long)."</option>\n";
					}
					else if (substr($conference_center_greeting, -3) == "wav" || substr($conference_center_greeting, -3) == "mp3") {
						echo "		<option value='".escape($conference_center_greeting)."' selected='selected'>".escape($conference_center_greeting)."</option>\n";
					}
					else {
						echo "		<option value='".escape($conference_center_greeting)."' selected='selected'>".escape($conference_center_greeting)."</option>\n";
					}
					echo "</optgroup>\n";
				}
				unset($tmp_selected);
			}
		}
	echo "	</select>\n";
	echo "	<br />\n";
	echo "	".$text['description-conference_center_greeting']."\n";
	echo "</td>\n";
	echo "</tr>\n";

	echo "<tr>\n";
	echo "<td class='vncellreq' valign='top' align='left' nowrap='nowrap'>\n";
	echo "	".$text['label-conference_center_pin_length']."\n";
	echo "</td>\n";
	echo "<td class='vtable' align='left'>\n";
	echo "  <input class='formfld' type='text' name='conference_center_pin_length' maxlength='255' value='".escape($conference_center_pin_length)."'>\n";
	echo "<br />\n";
	echo $text['description-conference_center_pin_length']."\n";
	echo "</td>\n";
	echo "</tr>\n";

	echo "<tr>\n";
	echo "<td class='vncellreq' valign='top' align='left' nowrap='nowrap'>\n";
	echo "	".$text['label-conference_center_enabled']."\n";
	echo "</td>\n";
	echo "<td class='vtable' align='left'>\n";
	echo "	<select class='formfld' name='conference_center_enabled'>\n";
	echo "	<option value=''></option>\n";
	if ($conference_center_enabled == "true") {
		echo "	<option value='true' selected='selected'>".$text['label-true']."</option>\n";
	}
	else {
		echo "	<option value='true'>".$text['label-true']."</option>\n";
	}
	if ($conference_center_enabled == "false") {
		echo "	<option value='false' selected='selected'>".$text['label-false']."</option>\n";
	}
	else {
		echo "	<option value='false'>".$text['label-false']."</option>\n";
	}
	echo "	</select>\n";
	echo "<br />\n";
	echo $text['description-conference_center_enabled']."\n";
	echo "</td>\n";
	echo "</tr>\n";

	echo "<tr>\n";
	echo "<td class='vncell' valign='top' align='left' nowrap='nowrap'>\n";
	echo "	".$text['label-conference_center_description']."\n";
	echo "</td>\n";
	echo "<td class='vtable' align='left'>\n";
	echo "	<input class='formfld' type='text' name='conference_center_description' maxlength='255' value=\"".escape($conference_center_description)."\">\n";
	echo "<br />\n";
	echo $text['description-conference_center_description']."\n";
	echo "</td>\n";
	echo "</tr>\n";
	echo "	<tr>\n";
	echo "		<td colspan='2' align='right'>\n";
	if ($action == "update") {
		echo "			<input type='hidden' name='dialplan_uuid' value='".escape($dialplan_uuid)."'>\n";
		echo "			<input type='hidden' name='conference_center_uuid' value='".escape($conference_center_uuid)."'>\n";
	}
	echo "			<input type='submit' class='btn' value='".$text['button-save']."'>\n";
	echo "		</td>\n";
	echo "	</tr>";
	echo "</table>";
	echo "</form>";
	echo "<br /><br />";

//include the footer
	require_once "resources/footer.php";

?>
