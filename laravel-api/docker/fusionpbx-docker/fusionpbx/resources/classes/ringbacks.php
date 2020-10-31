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
	Matthew Vale <github@mafoo.org>
*/

if (!class_exists('ringbacks')) {
	class ringbacks {

		//define variables
		public $db;
		public $domain_uuid;
		private $ringtones_list;
		private $tones_list;
		private $music_list;
		private $recordings_list;
		private $default_ringback_label;
		
		//class constructor
		public function __construct() {
			//connect to the database if not connected
				if (!$this->db) {
					require_once "resources/classes/database.php";
					$database = new database;
					$database->connect();
					$this->db = $database->db;
				}

			//set the domain_uuid
				$this->domain_uuid = $_SESSION['domain_uuid'];

			//add multi-lingual support
				$language = new text;
				$text = $language->get();

			//get the ringtones
				$sql = "select * from v_vars ";
				$sql .= "where var_category = 'Ringtones' ";
				$sql .= "order by var_name asc ";
				$prep_statement = $this->db->prepare(check_sql($sql));
				$prep_statement->execute();
				$ringtones = $prep_statement->fetchAll(PDO::FETCH_NAMED);
				unset ($prep_statement, $sql);
				foreach($ringtones as $ringtone) {
					$ringtone = $ringtone['var_name'];
					$label = $text['label-'.$ringtone];
					if ($label == "") {
						$label = $ringtone;
					}
					$ringtones_list[$ringtone] = $label;
				}
				$this->ringtones_list = $ringtones_list;

			//get the default_ringback label
				/*
				$sql = "select * from v_vars where var_name = 'ringback' ";
				$prep_statement = $this->db->prepare(check_sql($sql));
				$prep_statement->execute();
				$result = $prep_statement->fetch();
				unset ($prep_statement, $sql);
				$default_ringback = (string) $result['var_value'];
				$default_ringback = preg_replace('/\A\$\${/',"",$default_ringback);
				$default_ringback = preg_replace('/}\z/',"",$default_ringback);
				#$label = $text['label-'.$default_ringback];
				#if($label == "") {
					$label = $default_ringback;
				#}
				$this->default_ringback_label = $label;
				unset($results, $default_ringback, $label);
				*/

			//get the tones
				require_once "resources/classes/tones.php";
				$tones = new tones;
				$this->tones_list = $tones->tones_list();

			//get music on hold	and recordings
				if (is_dir($_SERVER["PROJECT_ROOT"].'/app/music_on_hold')) {
					require_once "app/music_on_hold/resources/classes/switch_music_on_hold.php";
					$music = new switch_music_on_hold;
					$this->music_list = $music->get();
				}
				if (is_dir($_SERVER["PROJECT_ROOT"].'/app/recordings')) {
					require_once "app/recordings/resources/classes/switch_recordings.php";
					$recordings = new switch_recordings;
					$this->recordings_list = $recordings->list_recordings();
				}
		}

		public function select ($name, $selected) {
			//add multi-lingual support
				$language = new text;
				$text = $language->get();

			//start the select
				$select = "<select class='formfld' name='".$name."' id='".$name."' style='width: auto;'>\n";

			//music list
				if (count($this->music_list) > 0) {
					$select .= "	<optgroup label='".$text['label-music_on_hold']."'>\n";
					$previous_name = '';
					foreach($this->music_list as $row) {
						if ($previous_name != $row['music_on_hold_name']) {
							$name = '';
							if (strlen($row['domain_uuid']) > 0) {
								$name = $row['domain_name'].'/';	
							}
							$name .= $row['music_on_hold_name'];
							$select .= "		<option value='local_stream://".$name."' ".(($selected == "local_stream://".$name) ? 'selected="selected"' : null).">".$row['music_on_hold_name']."</option>\n";
						}
						$previous_name = $row['music_on_hold_name'];
					}
					$select .= "	</optgroup>\n";
				}

			//recordings
				if (sizeof($this->recordings_list) > 0) {
					$select .= "	<optgroup label='".$text['label-recordings']."'>";
					foreach($this->recordings_list as $recording_value => $recording_name){
						$select .= "		<option value='".$recording_value."' ".(($selected == $recording_value) ? 'selected="selected"' : '').">".$recording_name."</option>\n";
					}
					$select .= "	</optgroup>\n";
				}

			//streams
				if (is_dir($_SERVER["PROJECT_ROOT"].'/app/streams')) {
					$sql = "select * from v_streams ";
					$sql .= "where (domain_uuid = '".$this->domain_uuid."' or domain_uuid is null) ";
					$sql .= "and stream_enabled = 'true' ";
					$sql .= "order by stream_name asc ";
					$prep_statement = $this->db->prepare(check_sql($sql));
					$prep_statement->execute();
					$streams = $prep_statement->fetchAll(PDO::FETCH_NAMED);
					if (sizeof($streams) > 0) {
						$select .= "	<optgroup label='".$text['label-streams']."'>";
						foreach($streams as $row){
							$select .= "		<option value='".$row['stream_location']."' ".(($selected == $row['stream_location']) ? 'selected="selected"' : null).">".$row['stream_name']."</option>\n";
						}
						$select .= "	</optgroup>\n";
					}
				}

			//ringtones
				if (sizeof($this->ringtones_list) > 0) {
					$selected_ringtone = $selected;
					$selected_ringtone = preg_replace('/\A\${/',"",$selected_ringtone);
					$selected_ringtone = preg_replace('/}\z/',"",$selected_ringtone);
					$select .= "	<optgroup label='".$text['label-ringtones']."'>";
					//$select .= "		<option value='default_ringtones'".(($selected == "default_ringback") ? ' selected="selected"' : '').">".$text['label-default']." (".$this->default_ringtone_label.")</option>\n";
					foreach($this->ringtones_list as $ringtone_value => $ringtone_name) {
						$select .= "		<option value='\${".$ringtone_value."}'".(($selected_ringtone == $ringtone_value) ? ' selected="selected"' : '').">".$ringtone_name."</option>\n";
					}
					$select .= "	</optgroup>\n";
					unset($selected_ringtone);
				}

			//tones
				if (sizeof($this->tones_list) > 0) {
					$selected_tone = $selected;
					$selected_tone = preg_replace('/\A\${/',"",$selected_tone);
					$selected_tone = preg_replace('/}\z/',"",$selected_tone);
					$select .= "	<optgroup label='".$text['label-tones']."'>";
					foreach($this->tones_list as $tone_value => $tone_name) {
						$select .= "		<option value='\${".$tone_value."}'".(($selected_tone == $tone_value) ? ' selected="selected"' : '').">".$tone_name."</option>\n";
					}
					$select .= "	</optgroup>\n";
					unset($selected_tone);
				}

			//end the select and return it
				$select .= "</select>\n";
				return $select;
		}
	}
}

?>
