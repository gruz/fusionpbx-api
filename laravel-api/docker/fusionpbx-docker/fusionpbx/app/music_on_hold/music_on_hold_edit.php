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
	Portions created by the Initial Developer are Copyright (C) 2016-2018
	the Initial Developer. All Rights Reserved.

	Contributor(s):
	Mark J Crane <markjcrane@fusionpbx.com>
*/

//includes
	require_once "root.php";
	require_once "resources/require.php";

//check permissions
	require_once "resources/check_auth.php";
	if (permission_exists('music_on_hold_add') || permission_exists('music_on_hold_edit')) {
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
		$music_on_hold_uuid = check_str($_REQUEST["id"]);
	}
	else {
		$action = "add";
	}

//get http post variables and set them to php variables
	if (count($_POST) > 0) {
		if (permission_exists('music_on_hold_domain')) {
			$domain_uuid = check_str($_POST["domain_uuid"]);
		}
		$music_on_hold_name = check_str($_POST["music_on_hold_name"]);
		$music_on_hold_path = check_str($_POST["music_on_hold_path"]);
		$music_on_hold_rate = check_str($_POST["music_on_hold_rate"]);
		$music_on_hold_shuffle = check_str($_POST["music_on_hold_shuffle"]);
		$music_on_hold_channels = check_str($_POST["music_on_hold_channels"]);
		$music_on_hold_interval = check_str($_POST["music_on_hold_interval"]);
		$music_on_hold_timer_name = check_str($_POST["music_on_hold_timer_name"]);
		$music_on_hold_chime_list = check_str($_POST["music_on_hold_chime_list"]);
		$music_on_hold_chime_freq = check_str($_POST["music_on_hold_chime_freq"]);
		$music_on_hold_chime_max = check_str($_POST["music_on_hold_chime_max"]);
	}

//add or update the data
	if (count($_POST) > 0 && strlen($_POST["persistformvar"]) == 0) {

		//get the uuid
			if ($action == "update") {
				$music_on_hold_uuid = check_str($_POST["music_on_hold_uuid"]);
			}

		//check for all required data
			$msg = '';
			if (strlen($music_on_hold_name) == 0) { $msg .= $text['message-required']." ".$text['label-name']."<br>\n"; }
			if (strlen($music_on_hold_path) == 0) { $msg .= $text['message-required']." ".$text['label-path']."<br>\n"; }
			//if (strlen($music_on_hold_rate) == 0) { $msg .= $text['message-required']." ".$text['label-rate']."<br>\n"; }
			if (strlen($music_on_hold_shuffle) == 0) { $msg .= $text['message-required']." ".$text['label-shuffle']."<br>\n"; }
			if (strlen($music_on_hold_channels) == 0) { $msg .= $text['message-required']." ".$text['label-channels']."<br>\n"; }
			//if (strlen($music_on_hold_interval) == 0) { $msg .= $text['message-required']." ".$text['label-interval']."<br>\n"; }
			//if (strlen($music_on_hold_timer_name) == 0) { $msg .= $text['message-required']." ".$text['label-timer_name']."<br>\n"; }
			//if (strlen($music_on_hold_chime_list) == 0) { $msg .= $text['message-required']." ".$text['label-chime_list']."<br>\n"; }
			//if (strlen($music_on_hold_chime_freq) == 0) { $msg .= $text['message-required']." ".$text['label-chime_freq']."<br>\n"; }
			//if (strlen($music_on_hold_chime_max) == 0) { $msg .= $text['message-required']." ".$text['label-chime_max']."<br>\n"; }
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
				if ($action == "add" && permission_exists('music_on_hold_add')) {
					//insert the new music on hold
						$sql = "insert into v_music_on_hold ";
						$sql .= "(";
						$sql .= "domain_uuid, ";
						$sql .= "music_on_hold_uuid, ";
						$sql .= "music_on_hold_name, ";
						$sql .= "music_on_hold_path, ";
						$sql .= "music_on_hold_rate, ";
						$sql .= "music_on_hold_shuffle, ";
						$sql .= "music_on_hold_channels, ";
						$sql .= "music_on_hold_interval, ";
						$sql .= "music_on_hold_timer_name, ";
						$sql .= "music_on_hold_chime_list, ";
						$sql .= "music_on_hold_chime_freq, ";
						$sql .= "music_on_hold_chime_max ";
						$sql .= ")";
						$sql .= "values ";
						$sql .= "(";
						if (permission_exists('music_on_hold_domain')) {
							if (strlen($domain_uuid) == null) {
								$sql .= "null, ";
							}
							else {
								$sql .= "'".$domain_uuid."', ";
							}
						}
						else {
							$sql .= "'".$_SESSION['domain_uuid']."', ";
						}
						$sql .= "'".uuid()."', ";
						$sql .= "'$music_on_hold_name', ";
						$sql .= "'$music_on_hold_path', ";
						if (strlen($music_on_hold_rate) == 0) { $sql .= "null, "; } else { $sql .= "'$music_on_hold_rate', "; }
						$sql .= "'$music_on_hold_shuffle', ";
						if (strlen($music_on_hold_channels) == 0) { $sql .= "null, "; } else { $sql .= "'$music_on_hold_channels', "; }
						if (strlen($music_on_hold_interval) == 0) { $sql .= "null, "; } else { $sql .= "'$music_on_hold_interval', "; }
						$sql .= "'$music_on_hold_timer_name', ";
						$sql .= "'$music_on_hold_chime_list', ";
						if (strlen($music_on_hold_chime_freq) == 0) { $sql .= "null, "; } else { $sql .= "'$music_on_hold_chime_freq', "; }
						if (strlen($music_on_hold_chime_max) == 0) { $sql .= "null "; } else { $sql .= "'$music_on_hold_chime_max' "; }
						$sql .= ")";
						$db->exec(check_sql($sql));
						unset($sql);

					//clear the cache
						$cache = new cache;
						$cache->delete("configuration:local_stream.conf");

					//reload mod local stream
						$music = new switch_music_on_hold;
						$music->reload();

					//set the message and redirect the user
						messages::add($text['message-add']);
						header("Location: music_on_hold.php");
						return;
				} //if ($action == "add")

				if ($action == "update" && permission_exists('music_on_hold_edit')) {
					//update the stream settings
						$sql = "update v_music_on_hold set ";
						if (permission_exists('music_on_hold_domain')) {
							if (strlen($domain_uuid) == null) {
								$sql .= "domain_uuid = null, ";
							}
							else {
								$sql .= "domain_uuid = '$domain_uuid', ";
							}
						}
						else {
							$sql .= "domain_uuid = '".$_SESSION['domain_uuid']."', ";
						}
						$sql .= "music_on_hold_name = '$music_on_hold_name', ";
						$sql .= "music_on_hold_path = '$music_on_hold_path', ";
						if (strlen($music_on_hold_rate) == 0) { $sql .= "music_on_hold_rate = null, "; } else { $sql .= "music_on_hold_rate = '$music_on_hold_rate', "; }
						$sql .= "music_on_hold_shuffle = '$music_on_hold_shuffle', ";
						if (strlen($music_on_hold_channels) == 0) { $sql .= "music_on_hold_channels = null, "; } else { $sql .= "music_on_hold_channels = '$music_on_hold_channels', "; }
						if (strlen($music_on_hold_interval) == 0) { $sql .= "music_on_hold_interval = null, "; } else { $sql .= "music_on_hold_interval = '$music_on_hold_interval', "; }
						$sql .= "music_on_hold_timer_name = '$music_on_hold_timer_name', ";
						$sql .= "music_on_hold_chime_list = '$music_on_hold_chime_list', ";
						if (strlen($music_on_hold_chime_freq) == 0) { $sql .= "music_on_hold_chime_freq = null, "; } else { $sql .= "music_on_hold_chime_freq = '$music_on_hold_chime_freq', "; }
						if (strlen($music_on_hold_chime_max) == 0) { $sql .= "music_on_hold_chime_max = null "; } else { $sql .= "music_on_hold_chime_max = '$music_on_hold_chime_max' "; }
						$sql .= "where music_on_hold_uuid = '$music_on_hold_uuid' ";
						$db->exec(check_sql($sql));
						unset($sql);

					//clear the cache
						$cache = new cache;
						$cache->delete("configuration:local_stream.conf");

					//reload mod local stream
						$music = new switch_music_on_hold;
						$music->reload();

					//set the message and redirect the user
						messages::add($text['message-update']);
						header("Location: music_on_hold.php");
						return;
				} //if ($action == "update")
			} //if ($_POST["persistformvar"] != "true")
	} //(count($_POST)>0 && strlen($_POST["persistformvar"]) == 0)

//pre-populate the form
	if (count($_GET) > 0 && $_POST["persistformvar"] != "true") {
		$music_on_hold_uuid = check_str($_GET["id"]);
		$sql = "select * from v_music_on_hold ";
		$sql .= "where ( ";
		$sql .= "	domain_uuid = '$domain_uuid' ";
		$sql .= "	or domain_uuid is null ";
		$sql .= ") ";
		$sql .= "and music_on_hold_uuid = '$music_on_hold_uuid' ";
		$prep_statement = $db->prepare(check_sql($sql));
		$prep_statement->execute();
		$result = $prep_statement->fetchAll(PDO::FETCH_NAMED);
		foreach ($result as &$row) {
			$domain_uuid = $row["domain_uuid"];
			$music_on_hold_name = $row["music_on_hold_name"];
			$music_on_hold_path = $row["music_on_hold_path"];
			$music_on_hold_rate = $row["music_on_hold_rate"];
			$music_on_hold_shuffle = $row["music_on_hold_shuffle"];
			$music_on_hold_channels = $row["music_on_hold_channels"];
			$music_on_hold_interval = $row["music_on_hold_interval"];
			$music_on_hold_timer_name = $row["music_on_hold_timer_name"];
			$music_on_hold_chime_list = $row["music_on_hold_chime_list"];
			$music_on_hold_chime_freq = $row["music_on_hold_chime_freq"];
			$music_on_hold_chime_max = $row["music_on_hold_chime_max"];
		}
		unset ($prep_statement);
	}

//show the header
	require_once "resources/header.php";

//show the content
	echo "<form name='frm' id='frm' method='post' action=''>\n";
	echo "<table width='100%'  border='0' cellpadding='0' cellspacing='0'>\n";
	echo "<tr>\n";
	echo "<td align='left' width='30%' nowrap='nowrap' valign='top'><b>".$text['title-music_on_hold']."</b><br><br></td>\n";
	echo "<td width='70%' align='right' valign='top'>\n";
	echo "	<input type='button' class='btn' name='' alt='".$text['button-back']."' onclick=\"window.location='music_on_hold.php'\" value='".$text['button-back']."'>";
	echo "	<input type='submit' name='submit' class='btn' value='".$text['button-save']."'>";
	echo "</td>\n";
	echo "</tr>\n";

	echo "<tr>\n";
	echo "<td class='vncellreq' valign='top' align='left' nowrap='nowrap'>\n";
	echo "	".$text['label-name']."\n";
	echo "</td>\n";
	echo "<td class='vtable' align='left'>\n";
	echo "	<input class='formfld' type='text' name='music_on_hold_name' maxlength='255' value=\"".escape($music_on_hold_name)."\">\n";
	echo "<br />\n";
	echo $text['description-music_on_hold_name']."\n";
	echo "</td>\n";
	echo "</tr>\n";

	echo "<tr>\n";
	echo "<td class='vncellreq' valign='top' align='left' nowrap='nowrap'>\n";
	echo "	".$text['label-path']."\n";
	echo "</td>\n";
	echo "<td class='vtable' align='left'>\n";
	echo "	<input class='formfld' type='text' name='music_on_hold_path' maxlength='255' value=\"".escape($music_on_hold_path)."\">\n";
	echo "<br />\n";
	echo $text['description-music_on_hold_path']."\n";
	echo "</td>\n";
	echo "</tr>\n";

	echo "<tr>\n";
	echo "<td class='vncell' valign='top' align='left' nowrap='nowrap'>\n";
	echo "	".$text['label-rate']."\n";
	echo "</td>\n";
	echo "<td class='vtable' align='left'>\n";
	echo "	<select class='formfld' name='music_on_hold_rate'>\n";
	if ($music_on_hold_rate == "") {
		echo "	<option value='' selected='selected'>".$text['option-default']."</option>\n";
	}
	else {
		echo "	<option value=''>".$text['option-default']."</option>\n";
	}
	if ($music_on_hold_rate == "8000") {
		echo "	<option value='8000' selected='selected'>8000</option>\n";
	}
	else {
		echo "	<option value='8000'>8000</option>\n";
	}
	if ($music_on_hold_rate == "16000") {
		echo "	<option value='16000' selected='selected'>16000</option>\n";
	}
	else {
		echo "	<option value='16000'>16000</option>\n";
	}
	if ($music_on_hold_rate == "32000") {
		echo "	<option value='32000' selected='selected'>32000</option>\n";
	}
	else {
		echo "	<option value='32000'>32000</option>\n";
	}
	if ($music_on_hold_rate == "48000") {
		echo "	<option value='48000' selected='selected'>48000</option>\n";
	}
	else {
		echo "	<option value='48000'>48000</option>\n";
	}
	echo "	</select>\n";
	echo "<br />\n";
	echo $text['description-music_on_hold_rate']."\n";
	echo "</td>\n";
	echo "</tr>\n";

	echo "<tr>\n";
	echo "<td class='vncellreq' valign='top' align='left' nowrap='nowrap'>\n";
	echo "	".$text['label-shuffle']."\n";
	echo "</td>\n";
	echo "<td class='vtable' align='left'>\n";
	echo "	<select class='formfld' name='music_on_hold_shuffle'>\n";
	echo "	<option value=''></option>\n";
	if ($music_on_hold_shuffle == "true") {
		echo "	<option value='true' selected='selected'>".$text['label-true']."</option>\n";
	}
	else {
		echo "	<option value='true'>".$text['label-true']."</option>\n";
	}
	if ($music_on_hold_shuffle == "false") {
		echo "	<option value='false' selected='selected'>".$text['label-false']."</option>\n";
	}
	else {
		echo "	<option value='false'>".$text['label-false']."</option>\n";
	}
	echo "	</select>\n";
	echo "<br />\n";
	echo $text['description-music_on_hold_shuffle']."\n";
	echo "</td>\n";
	echo "</tr>\n";

	echo "<tr>\n";
	echo "<td class='vncellreq' valign='top' align='left' nowrap='nowrap'>\n";
	echo "	".$text['label-channels']."\n";
	echo "</td>\n";
	echo "<td class='vtable' align='left'>\n";
	echo "	<select name='music_on_hold_channels' class='formfld'>\n";
	echo "		<option value='1' ".(($music_on_hold_channels == '2') ? 'selected' : null).">".$text['label-mono']."</option>\n";
	echo "		<option value='2' ".(($music_on_hold_channels == '2') ? 'selected' : null).">".$text['label-stereo']."</option>\n";
	echo "	</select>\n";
	echo "<br />\n";
	echo $text['description-music_on_hold_channels']."\n";
	echo "</td>\n";
	echo "</tr>\n";

	echo "<tr>\n";
	echo "<td class='vncell' valign='top' align='left' nowrap='nowrap'>\n";
	echo "	".$text['label-interval']."\n";
	echo "</td>\n";
	echo "<td class='vtable' align='left'>\n";
	echo "  <input class='formfld' type='text' name='music_on_hold_interval' maxlength='255' value='".escape($music_on_hold_interval)."'>\n";
	echo "<br />\n";
	echo $text['description-music_on_hold_interval']."\n";
	echo "</td>\n";
	echo "</tr>\n";

	echo "<tr>\n";
	echo "<td class='vncellreq' valign='top' align='left' nowrap='nowrap'>\n";
	echo "	".$text['label-timer_name']."\n";
	echo "</td>\n";
	echo "<td class='vtable' align='left'>\n";
	echo "	<input class='formfld' type='text' name='music_on_hold_timer_name' maxlength='255' value=\"".escape($music_on_hold_timer_name)."\">\n";
	echo "<br />\n";
	echo $text['description-music_on_hold_timer_name']."\n";
	echo "</td>\n";
	echo "</tr>\n";

	echo "<tr>\n";
	echo "<td class='vncell'>\n";
	echo "	".$text['label-chime_list']."\n";
	echo "</td>\n";
	echo "<td class='vtable' align='left'>\n";
	echo "	<select name='music_on_hold_chime_list' class='formfld' style='width: 350px;' ".((permission_exists('music_on_hold_path')) ? "onchange='changeToInput(this);'" : null).">\n";
	echo "		<option value=''></option>\n";
	//misc optgroup
		/*
		if (if_group("superadmin")) {
			echo "<optgroup label='Misc'>\n";
			echo "	<option value='phrase:'>phrase:</option>\n";
			echo "	<option value='say:'>say:</option>\n";
			echo "	<option value='tone_stream:'>tone_stream:</option>\n";
			echo "</optgroup>\n";
		}
		*/
	//recordings
		$tmp_selected = false;
		$sql = "select * from v_recordings where domain_uuid = '".$domain_uuid."' ";
		$prep_statement = $db->prepare(check_sql($sql));
		$prep_statement->execute();
		$recordings = $prep_statement->fetchAll(PDO::FETCH_NAMED);
		if (count($recordings) > 0) {
			echo "<optgroup label='Recordings'>\n";
			foreach ($recordings as &$row) {
				$recording_name = $row["recording_name"];
				$recording_filename = $row["recording_filename"];
				if ($music_on_hold_chime_list == $_SESSION['switch']['recordings']['dir']."/".$_SESSION['domain_name']."/".$recording_filename && strlen($music_on_hold_chime_list) > 0) {
					$tmp_selected = true;
					echo "	<option value='".escape($_SESSION['switch']['recordings']['dir'])."/".escape($_SESSION['domain_name'])."/".escape($recording_filename)."' selected='selected'>".escape($recording_name)."</option>\n";
				}
				else if ($music_on_hold_chime_list == $recording_filename && strlen($music_on_hold_chime_list) > 0) {
					$tmp_selected = true;
					echo "	<option value='".escape($recording_filename)."' selected='selected'>".escape($recording_name)."</option>\n";
				}
				else {
					echo "	<option value='".escape($recording_filename)."'>".escape($recording_name)."</option>\n";
				}
			}
			echo "</optgroup>\n";
		}
	//phrases
		$sql = "select * from v_phrases where domain_uuid = '".$domain_uuid."' ";
		$prep_statement = $db->prepare(check_sql($sql));
		$prep_statement->execute();
		$result = $prep_statement->fetchAll(PDO::FETCH_NAMED);
		if (count($result) > 0) {
			echo "<optgroup label='Phrases'>\n";
			foreach ($result as &$row) {
				if ($music_on_hold_chime_list == "phrase:".$row["phrase_uuid"]) {
					$tmp_selected = true;
					echo "	<option value='phrase:".escape($row["phrase_uuid"])."' selected='selected'>".escape($row["phrase_name"])."</option>\n";
				}
				else {
					echo "	<option value='phrase:".escape($row["phrase_uuid"])."'>".escape($row["phrase_name"])."</option>\n";
				}
			}
			unset ($prep_statement);
			echo "</optgroup>\n";
		}
	//sounds
		$file = new file;
		$sound_files = $file->sounds();
		if (is_array($sound_files)) {
			echo "<optgroup label='Sounds'>\n";
			foreach ($sound_files as $value) {
				if (strlen($value) > 0) {
					if (substr($music_on_hold_chime_list, 0, 71) == "\$\${sounds_dir}/\${default_language}/\${default_dialect}/\${default_voice}/") {
						$music_on_hold_chime_list = substr($music_on_hold_chime_list, 71);
					}
					if ($music_on_hold_chime_list == $value) {
						$tmp_selected = true;
						echo "	<option value='".escape($value)."' selected='selected'>".escape($value)."</option>\n";
					}
					else {
						echo "	<option value='".escape($value)."'>".escape($value)."</option>\n";
					}
				}
			}
			echo "</optgroup>\n";
		}
	//select
		if (if_group("superadmin")) {
			if (!$tmp_selected && strlen($music_on_hold_chime_list) > 0) {
				echo "<optgroup label='Selected'>\n";
				if (file_exists($_SESSION['switch']['recordings']['dir']."/".$_SESSION['domain_name']."/".$music_on_hold_chime_list)) {
					echo "	<option value='".escape($_SESSION['switch']['recordings']['dir'])."/".escape($_SESSION['domain_name'])."/".escape($music_on_hold_chime_list)."' selected='selected'>".escape($music_on_hold_chime_list)."</option>\n";
				}
				else if (substr($music_on_hold_chime_list, -3) == "wav" || substr($music_on_hold_chime_list, -3) == "mp3") {
					echo "	<option value='".escape($music_on_hold_chime_list)."' selected='selected'>".escape($music_on_hold_chime_list)."</option>\n";
				}
				echo "</optgroup>\n";
			}
			unset($tmp_selected);
		}
	echo "	</select>\n";
	echo "</td>\n";
	echo "</tr>\n";

	echo "<tr>\n";
	echo "<td class='vncell' valign='top' align='left' nowrap='nowrap'>\n";
	echo "	".$text['label-chime_frequency']."\n";
	echo "</td>\n";
	echo "<td class='vtable' align='left'>\n";
	echo "	<input class='formfld' type='text' name='music_on_hold_chime_freq' maxlength='255' value=\"".escape($music_on_hold_chime_freq)."\">\n";
	echo "<br />\n";
	echo $text['description-music_on_hold_chime_freq']."\n";
	echo "</td>\n";
	echo "</tr>\n";

	echo "<tr>\n";
	echo "<td class='vncell' valign='top' align='left' nowrap='nowrap'>\n";
	echo "	".$text['label-chime_maximum']."\n";
	echo "</td>\n";
	echo "<td class='vtable' align='left'>\n";
	echo "	<input class='formfld' type='text' name='music_on_hold_chime_max' maxlength='255' value=\"".escape($music_on_hold_chime_max)."\">\n";
	echo "<br />\n";
	echo $text['description-music_on_hold_chime_max']."\n";
	echo "</td>\n";
	echo "</tr>\n";

	if (permission_exists('music_on_hold_domain')) {
		echo "<tr>\n";
		echo "<td class='vncell' valign='top' nowrap='nowrap'>\n";
		echo "	".$text['label-domain']."\n";
		echo "</td>\n";
		echo "<td class='vtable'>\n";
		echo "	<select name='domain_uuid' class='formfld'>\n";
		if (strlen($domain_uuid) == 0) {
			echo "		<option value='' selected='selected'>".$text['label-global']."</option>\n";
		}
		else {
			echo "		<option value=''>".$text['label-global']."</option>\n";
		}
		foreach ($_SESSION['domains'] as $row) {
			if ($row['domain_uuid'] == $domain_uuid) {
				echo "		<option value='".escape($row['domain_uuid'])."' selected='selected'>".escape($row['domain_name'])."</option>\n";
			}
			else {
				echo "		<option value='".escape($row['domain_uuid'])."'>".escape($row['domain_name'])."</option>\n";
			}
		}
		echo "	</select>\n";
		echo "</td>\n";
		echo "</tr>\n";
	}

	echo "	<tr>\n";
	echo "		<td colspan='2' align='right'>\n";
	if ($action == "update") {
		echo "				<input type='hidden' name='music_on_hold_uuid' value='".escape($music_on_hold_uuid)."'>\n";
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
