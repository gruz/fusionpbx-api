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
	Luis Daniel Lucio Quiroz <dlucio@okay.com.mx>
*/

//check the permission
	if (defined('STDIN')) {
		$document_root = str_replace("\\", "/", $_SERVER["PHP_SELF"]);
		preg_match("/^(.*)\/app\/.*$/", $document_root, $matches);
		$document_root = $matches[1];
		set_include_path($document_root);
		$_SERVER["DOCUMENT_ROOT"] = $document_root;
		require_once "resources/require.php";
	}
	else {
		include "root.php";
		require_once "resources/require.php";
		require_once "resources/pdo.php";
	}

//set debug
	$debug = false; //true //false
	if ($debug){
		$time5 = microtime(true);
		$insert_time=$insert_count=0;
	}

	function xml_cdr_log($msg) {
		global $debug;
		if (!$debug) {
			return;
		}
		$fp = fopen($_SESSION['server']['temp']['dir'].'/xml_cdr.log', 'a+');
		if (!$fp) {
			return;
		}
		fwrite($fp, $msg);
		fclose($fp);
	}

//increase limits
	set_time_limit(3600);
	ini_set('memory_limit', '256M');
	ini_set("precision", 6);

//set pdo attribute that enables exception handling
	$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

//add rating functions if the billing is installed
	if (file_exists($_SERVER["PROJECT_ROOT"]."/app/billing/app_config.php")){
		require_once "app/billing/resources/functions/rating.php";
	}

	function accept_b_leg($xml){
		// if no filter set allow all for backward compatibility
			if(empty($_SESSION['cdr']['b_leg'])){
				return true;
			}
		// filter out by call direction
			if(in_array(@$xml->variables->call_direction, $_SESSION['cdr']['b_leg'])){
				return true;
			}
		// Disable cdr write
			return false;
	}

//define the process_xml_cdr function
	function process_xml_cdr($db, $leg, $xml_string) {
		//set global variable
			global $debug;

		//fix the xml by escaping the contents of <sip_full_XXX>
			$xml_string = preg_replace_callback("/<([^><]+)>(.*?[><].*?)<\/\g1>/",
				function ($matches) {
					return '<' . $matches[1] . '>' .
						str_replace(">", "&gt;",
							str_replace("<", "&lt;", $matches[2])
						) .
					'</' . $matches[1] . '>';
				},
				$xml_string
			);

		//parse the xml to get the call detail record info
			try {
				//send info to lthe log
				xml_cdr_log($xml_string);

				//disable xml entities
				libxml_disable_entity_loader(true);

				//load the string into an xml object
				$xml = simplexml_load_string($xml_string, 'SimpleXMLElement', LIBXML_NOCDATA);

				//send info to the log
				xml_cdr_log("\nxml load done\n");
			}
			catch(Exception $e) {
				echo $e->getMessage();
				xml_cdr_log("\nfail loadxml: " . $e->getMessage() . "\n");
			}

		//convert the xml object to json
			$json = check_str(json_encode($xml));
			
		//convert json to an array
			$array = json_decode($json, true);

		//filter out b-legs
			if($leg == 'b'){
				if(!accept_b_leg($xml)){
					return;
				}
			}

		//prepare the database object
			require_once "resources/classes/database.php";
			$database = new database;
			$database->table = "v_xml_cdr";
		//caller info
			$database->fields['caller_destination'] = check_str(urldecode($xml->variables->caller_destination));
			$database->fields['source_number'] = check_str(urldecode($xml->variables->caller_id_number));
		//misc
			$uuid = check_str(urldecode($xml->variables->uuid));
			$database->fields['xml_cdr_uuid'] = $uuid;
			$database->fields['accountcode'] = check_str(urldecode($xml->variables->accountcode));
			$database->fields['default_language'] = check_str(urldecode($xml->variables->default_language));
			$database->fields['bridge_uuid'] = check_str(urldecode($xml->variables->bridge_uuid));
			//$database->fields['digits_dialed'] = check_str(urldecode($xml->variables->digits_dialed));
			$database->fields['sip_hangup_disposition'] = check_str(urldecode($xml->variables->sip_hangup_disposition));
			$database->fields['pin_number'] = check_str(urldecode($xml->variables->pin_number));
		//time
			$database->fields['start_epoch'] = check_str(urldecode($xml->variables->start_epoch));
			$start_stamp = check_str(urldecode($xml->variables->start_stamp));
			$database->fields['start_stamp'] = $start_stamp;
			$database->fields['answer_stamp'] = check_str(urldecode($xml->variables->answer_stamp));
			$database->fields['answer_epoch'] = check_str(urldecode($xml->variables->answer_epoch));
			$database->fields['end_epoch'] = check_str(urldecode($xml->variables->end_epoch));
			$database->fields['end_stamp'] = check_str(urldecode($xml->variables->end_stamp));
			$database->fields['duration'] = check_str(urldecode($xml->variables->duration));
			$database->fields['mduration'] = check_str(urldecode($xml->variables->mduration));
			$database->fields['billsec'] = check_str(urldecode($xml->variables->billsec));
			$database->fields['billmsec'] = check_str(urldecode($xml->variables->billmsec));
		//codecs
			$database->fields['read_codec'] = check_str(urldecode($xml->variables->read_codec));
			$database->fields['read_rate'] = check_str(urldecode($xml->variables->read_rate));
			$database->fields['write_codec'] = check_str(urldecode($xml->variables->write_codec));
			$database->fields['write_rate'] = check_str(urldecode($xml->variables->write_rate));
			$database->fields['remote_media_ip'] = check_str(urldecode($xml->variables->remote_media_ip));
			$database->fields['hangup_cause'] = check_str(urldecode($xml->variables->hangup_cause));
			$database->fields['hangup_cause_q850'] = check_str(urldecode($xml->variables->hangup_cause_q850));
		//call center
			$database->fields['cc_side'] = check_str(urldecode($xml->variables->cc_side));
			$database->fields['cc_member_uuid'] = check_str(urldecode($xml->variables->cc_member_uuid));
			$database->fields['cc_queue_joined_epoch'] = check_str(urldecode($xml->variables->cc_queue_joined_epoch));
			$database->fields['cc_queue'] = check_str(urldecode($xml->variables->cc_queue));
			$database->fields['cc_member_session_uuid'] = check_str(urldecode($xml->variables->cc_member_session_uuid));
			$database->fields['cc_agent_uuid'] = check_str(urldecode($xml->variables->cc_agent_uuid));
			$database->fields['cc_agent'] = check_str(urldecode($xml->variables->cc_agent));
			$database->fields['cc_agent_type'] = check_str(urldecode($xml->variables->cc_agent_type));
			$database->fields['cc_agent_bridged'] = check_str(urldecode($xml->variables->cc_agent_bridged));
			$database->fields['cc_queue_answered_epoch'] = check_str(urldecode($xml->variables->cc_queue_answered_epoch));
			$database->fields['cc_queue_terminated_epoch'] = check_str(urldecode($xml->variables->cc_queue_terminated_epoch));
			$database->fields['cc_queue_canceled_epoch'] = check_str(urldecode($xml->variables->cc_queue_canceled_epoch));
			$database->fields['cc_cancel_reason'] = check_str(urldecode($xml->variables->cc_cancel_reason));
			$database->fields['cc_cause'] = check_str(urldecode($xml->variables->cc_cause));
			$database->fields['waitsec'] = check_str(urldecode($xml->variables->waitsec));
		//app info
			$database->fields['last_app'] = check_str(urldecode($xml->variables->last_app));
			$database->fields['last_arg'] = check_str(urldecode($xml->variables->last_arg));
		//conference
			$database->fields['conference_name'] = check_str(urldecode($xml->variables->conference_name));
			$database->fields['conference_uuid'] = check_str(urldecode($xml->variables->conference_uuid));
			$database->fields['conference_member_id'] = check_str(urldecode($xml->variables->conference_member_id));
		//call quality
			$rtp_audio_in_mos = check_str(urldecode($xml->variables->rtp_audio_in_mos));
			if (strlen($rtp_audio_in_mos) > 0) {
				$database->fields['rtp_audio_in_mos'] = $rtp_audio_in_mos;
			}

		//get the caller details
			$database->fields['caller_id_name'] = urldecode($xml->variables->effective_caller_id_name);
			$database->fields['caller_id_number'] = urldecode($xml->variables->effective_caller_id_number);

		//get the values from the callflow.
			$x = 0;
			foreach ($xml->callflow as $row) {
				if ($x == 0) {
					$context = check_str(urldecode($row->caller_profile->context));
					$database->fields['destination_number'] = check_str(urldecode($row->caller_profile->destination_number));
					$database->fields['context'] = $context;
					$database->fields['network_addr'] = check_str(urldecode($row->caller_profile->network_addr));
				}
				if (strlen($database->fields['caller_id_name']) == 0) {
					$database->fields['caller_id_name'] = check_str(urldecode($row->caller_profile->caller_id_name));
				}
				if (strlen($database->fields['caller_id_number']) == 0) {
					$database->fields['caller_id_number'] = check_str(urldecode($row->caller_profile->caller_id_number));
				}
				$x++;
			}
			unset($x);

		//if last_sent_callee_id_number is set use it for the destination_number
			if (($leg == 'a') && (strlen($xml->variables->last_sent_callee_id_number) > 0)) {
				$database->fields['destination_number'] = urldecode($xml->variables->last_sent_callee_id_number);
			}

		//store the call leg
			$database->fields['leg'] = $leg;

		//store the call direction
			$database->fields['direction'] = check_str(urldecode($xml->variables->call_direction));

		//store post dial delay, in milliseconds
			$database->fields['pdd_ms'] = check_str(urldecode($xml->variables->progress_mediamsec) + urldecode($xml->variables->progressmsec));

		//get break down the date to year, month and day
			$start_time = strtotime($start_stamp);
			$start_year = date("Y", $start_time);
			$start_month = date("M", $start_time);
			$start_day = date("d", $start_time);

		//get the domain values from the xml
			$domain_name = check_str(urldecode($xml->variables->domain_name));
			$domain_uuid = check_str(urldecode($xml->variables->domain_uuid));

		//get the domain name from sip_req_host
			if (strlen($domain_name) == 0) {
				$domain_name = check_str(urldecode($xml->variables->sip_req_host));
			}

		//send the domain name to the cdr log
			xml_cdr_log("\ndomain_name is `$domain_name`; domain_uuid is '$domain_uuid'\n");

		//get the domain_uuid with the domain_name
			if (strlen($domain_uuid) == 0) {
				$sql = "select domain_uuid from v_domains ";
				if (strlen($domain_name) == 0 && $context != 'public' && $context != 'default') {
					$sql .= "where domain_name = '".$context."' ";
				}
				else {
					$sql .= "where domain_name = '".$domain_name."' ";
				}
				$row = $db->query($sql)->fetch();
				$domain_uuid = $row['domain_uuid'];
			}

		//set values in the database
			if (strlen($domain_uuid) > 0) {
				$database->domain_uuid = $domain_uuid;
				$database->fields['domain_uuid'] = $domain_uuid;
			}
			if (strlen($domain_name) > 0) {
				$database->fields['domain_name'] = $domain_name;
			}

		//save to the database in json format
			if ($_SESSION['cdr']['format']['text'] == "json" && $_SESSION['cdr']['storage']['text'] == "db") {
				$database->fields['json'] = $json;
			}

		//dynamic cdr fields
			if (is_array($_SESSION['cdr']['field'])) {
				foreach ($_SESSION['cdr']['field'] as $field) {
					$fields = explode(",", $field);
					$field_name = end($fields);
					if (count($fields) == 1) {
						$database->fields[$field_name] = urldecode($array['variables'][$fields[0]]);
					}
					if (count($fields) == 2) {
						$database->fields[$field_name] = urldecode($array[$fields[0]][$fields[1]]);
					}
					if (count($fields) == 3) {
						$database->fields[$field_name] = urldecode($array[$fields[0]][0][$fields[1]][$fields[2]]);
					}
					if (count($fields) == 4) {
						$database->fields[$field_name] = urldecode($array[$fields[0]][$fields[1]][$fields[2]][$fields[3]]);
					}
					if (count($fields) == 5) {
						$database->fields[$field_name] = urldecode($array[$fields[0]][$fields[1]][$fields[2]][$fields[3]][$fields[4]]);
					}
				}
			}

		//save to the database in xml format
			if ($_SESSION['cdr']['format']['text'] == "xml" && $_SESSION['cdr']['storage']['text'] == "db") {
				$database->fields['xml'] = check_str($xml_string);
			}

		//insert the check_str($extension_uuid)
			if (strlen($xml->variables->extension_uuid) > 0) {
				$database->fields['extension_uuid'] = check_str(urldecode($xml->variables->extension_uuid));
			}

		//get the recording details
			if (strlen($xml->variables->record_session) > 0) {
				$record_path = urldecode($xml->variables->record_path);
				$record_name = urldecode($xml->variables->record_name);
				$record_length = urldecode($xml->variables->record_seconds);
			}
			elseif (!isset($record_path) && urldecode($xml->variables->last_app) == "record_session") {
				$record_path = dirname(urldecode($xml->variables->last_arg));
				$record_name = basename(urldecode($xml->variables->last_arg));
				$record_length = urldecode($xml->variables->record_seconds);
			}
			elseif (strlen($xml->variables->record_name) > 0) {
				$record_path = urldecode($xml->variables->record_path);
				$record_name = urldecode($xml->variables->record_name);
				$record_length = urldecode($xml->variables->duration);
			}
			elseif (strlen($xml->variables->sofia_record_file) > 0) {
				$record_path = dirname(urldecode($xml->variables->sofia_record_file));
				$record_name = basename(urldecode($xml->variables->sofia_record_file));
				$record_length = urldecode($xml->variables->record_seconds);
			}
			elseif (strlen($xml->variables->cc_record_filename) > 0) {
				$record_path = dirname(urldecode($xml->variables->cc_record_filename));
				$record_name = basename(urldecode($xml->variables->cc_record_filename));
				$record_length = urldecode($xml->variables->record_seconds);
			}
			elseif (strlen($xml->variables->api_on_answer) > 0) {
				$command = str_replace("\n", " ", urldecode($xml->variables->api_on_answer));
				$parts = explode(" ", $command);
				if ($parts[0] == "uuid_record") {
					$recording = $parts[3];
					$record_path = dirname($recording);
					$record_name = basename($recording);
					$record_length = urldecode($xml->variables->duration);
				}
			}
			elseif (strlen($xml->variables->current_application_data) > 0) {
				$commands = explode(",", urldecode($xml->variables->current_application_data));
				foreach ($commands as $command) {
					$cmd = explode("=", $command);
					if ($cmd[0] == "api_on_answer") {
						$a = explode("]", $cmd[1]);
						$command = str_replace("'", "", $a[0]);
						$parts = explode(" ", $command);
						if ($parts[0] == "uuid_record") {
							$recording = $parts[3];
							$record_path = dirname($recording);
							$record_name = basename($recording);
							$record_length = urldecode($xml->variables->duration);
						}
					}
				}
			}
			if (!isset($record_name) || is_null ($record_name) || (strlen($record_name) == 0)) {
				$bridge_uuid = urldecode($xml->variables->bridge_uuid);
				$path = $_SESSION['switch']['recordings']['dir'].'/'.$domain_name.'/archive/'.$start_year.'/'.$start_month.'/'.$start_day;
				if (file_exists($path.'/'.$bridge_uuid.'.wav')) {
					$record_path = $path;
					$record_name = $bridge_uuid.'.wav';
					$record_length = urldecode($xml->variables->duration);
				} elseif (file_exists($path.'/'.$bridge_uuid.'.mp3')) {
					$record_path = $path;
					$record_name = $bridge_uuid.'.mp3';
					$record_length = urldecode($xml->variables->duration);
				}
			}
			if (!isset($record_name) || is_null ($record_name) || (strlen($record_name) == 0)) {
				$path = $_SESSION['switch']['recordings']['dir'].'/'.$domain_name.'/archive/'.$start_year.'/'.$start_month.'/'.$start_day;
				if (file_exists($path.'/'.$uuid.'.wav')) {
					$record_path = $path;
					$record_name = $uuid.'.wav';
					$record_length = urldecode($xml->variables->duration);
				} elseif (file_exists($path.'/'.$uuid.'.mp3')) {
					$record_path = $path;
					$record_name = $uuid.'.mp3';
					$record_length = urldecode($xml->variables->duration);
				}
			}

		//add the call recording
			if (isset($record_path) && isset($record_name) && file_exists($record_path.'/'.$record_name) && $record_length > 0) {
				//add to the xml cdr table
					$database->fields['record_path'] = $record_path;
					$database->fields['record_name'] = $record_name;
				//add to the call recordings table
					if (file_exists($_SERVER["PROJECT_ROOT"]."/app/call_recordings/app_config.php")) {
						//build the array
						$x = 0;
						$recordings['call_recordings'][$x]['call_recording_uuid'] = $uuid;
						$recordings['call_recordings'][$x]['domain_uuid'] = $domain_uuid;
						$recordings['call_recordings'][$x]['call_recording_name'] = $record_name;
						$recordings['call_recordings'][$x]['call_recording_path'] = $record_path;
						$recordings['call_recordings'][$x]['call_recording_length'] = $record_length;
						$recordings['call_recordings'][$x]['call_recording_date'] = urldecode($xml->variables->answer_stamp);
						$recordings['call_recordings'][$x]['call_direction'] = urldecode($xml->variables->call_direction);
						//$recordings['call_recordings'][$x]['call_recording_description']= $row['zzz'];
						//$recordings['call_recordings'][$x]['call_recording_base64']= $row['zzz'];

						//add the temporary permission
						$p = new permissions;
						$p->add("call_recording_add", "temp");
						$p->add("call_recording_edit", "temp");

						$recording_database = new database;
						$recording_database->app_name = 'call_recordings';
						$recording_database->app_uuid = '56165644-598d-4ed8-be01-d960bcb8ffed';
						$recording_database->domain_uuid = $domain_uuid;
						$recording_database->save($recordings);
						$message = $recording_database->message;
						unset($recordings);
	
						//remove the temporary permission
						$p->delete("call_recording_add", "temp");
						$p->delete("call_recording_edit", "temp");
					}
			}

		//billing information
			if (file_exists($_SERVER["PROJECT_ROOT"]."/app/billing/app_config.php")){
				$db2 = new database;
				$lcr_currency = (strlen($_SESSION['billing']['currency']['text'])?$_SESSION['billing']['currency']['text']:'USD');
				$accountcode = (strlen(urldecode($xml->variables->accountcode)))?check_str(urldecode($xml->variables->accountcode)):$domain_name;

				switch(check_str(urldecode($xml->variables->call_direction))){
					case "outbound":
							$destination_number = check_str(urldecode($xml->variables->lcr_query_digits));
							$destination_number_serie = number_series($destination_number);
							$database->fields['carrier_name'] = check_str(urldecode($xml->variables->lcr_carrier));
							$sql_rate ="SELECT v_lcr.connect_increment, v_lcr.talk_increment, v_lcr.currency FROM v_lcr, v_carriers WHERE v_carriers.carrier_name = '".$xml->variables->lcr_carrier."' AND v_lcr.rate=".$xml->variables->lcr_rate." AND v_lcr.lcr_direction = '".check_str(urldecode($xml->variables->call_direction))."' AND digits IN ($destination_number_serie) AND v_lcr.carrier_uuid = v_carriers.carrier_uuid  ORDER BY digits DESC, rate ASC limit 1";
							$sql_user_rate = "SELECT v_lcr.currency, connect_increment, talk_increment FROM v_lcr JOIN v_billings ON v_billings.type_value='$accountcode' WHERE v_lcr.carrier_uuid IS NULL AND v_lcr.lcr_direction = '".check_str(urldecode($xml->variables->call_direction))."' AND v_lcr.lcr_profile=v_billings.lcr_profile AND NOW() >= v_lcr.date_start AND NOW() < v_lcr.date_end AND digits IN ($destination_number_serie) ORDER BY digits DESC, rate ASC, date_start DESC LIMIT 1";
							if ($debug) {
								echo "sql_rate: $sql_rate\n";
								echo "sql_user_rate: $sql_user_rate\n";
							}

							$db2->sql = $sql_rate;
							$db2->result = $db2->execute();
							//print_r($db2->result);
							$lcr_currency = (strlen($db2->result[0]['currency'])?check_str($db2->result[0]['currency']):
								(strlen($_SESSION['billing']['currency']['text'])?$_SESSION['billing']['currency']['text']:'USD')
							);
							$lcr_rate = (strlen($xml->variables->lcr_rate)?$xml->variables->lcr_rate:0);
							$lcr_first_increment = (strlen($db2->result[0]['connect_increment'])?check_str($db2->result[0]['connect_increment']):60);
							$lcr_second_increment = (strlen($db2->result[0]['talk_increment'])?check_str($db2->result[0]['talk_increment']):60);
							unset($db2->sql);
							unset($db2->result);

							$db2->sql = $sql_user_rate;
							$db2->result = $db2->execute();
							$lcr_user_rate = (strlen($xml->variables->lcr_user_rate)?$xml->variables->lcr_user_rate:0.01);
							$lcr_user_first_increment = (strlen($db2->result[0]['connect_increment'])?check_str($db2->result[0]['connect_increment']):60);
							$lcr_user_second_increment = (strlen($db2->result[0]['talk_increment'])?check_str($db2->result[0]['talk_increment']):60);
							$lcr_user_currency = (strlen($db2->result[0]['currency'])?check_str($db2->result[0]['currency']):
								(strlen($_SESSION['billing']['currency']['text'])?$_SESSION['billing']['currency']['text']:'USD')
							);

							unset($db2->sql);
							unset($db2->result);
							break;
					case "inbound":
							$callee_number = check_str(urldecode($row->caller_profile->destination_number));
							$callee_number_serie = number_series($callee_number);
							$sql_user_rate = "SELECT v_lcr.currency, v_lcr.rate, v_lcr.connect_increment, v_lcr.talk_increment FROM v_lcr JOIN v_billings ON v_billings.type_value='$accountcode' WHERE v_lcr.carrier_uuid IS NULL AND v_lcr.lcr_direction = '".check_str(urldecode($xml->variables->call_direction))."' AND v_lcr.lcr_profile=v_billings.lcr_profile AND NOW() >= v_lcr.date_start AND NOW() < v_lcr.date_end AND digits IN ($callee_number_serie) ORDER BY digits DESC, rate ASC, date_start DESC LIMIT 1";

							if ($debug) {
								echo "sql_user_rate: $sql_user_rate\n";
							}

							$db2->sql = $sql_user_rate;
							$db2->result = $db2->execute();

							// If selling rate is found, then we fill with data, otherwise rate will be 0
							$lcr_currency = (strlen($db2->result[0]['currency'])?check_str($db2->result[0]['currency']):
								(strlen($_SESSION['billing']['currency']['text'])?$_SESSION['billing']['currency']['text']:'USD')
							);
							$lcr_user_rate = (strlen($db2->result[0]['rate']))?($db2->result[0]['rate']):0;
							$lcr_user_first_increment = (strlen($db2->result[0]['connect_increment']))?($db2->result[0]['connect_increment']):60;
							$lcr_user_second_increment = (strlen($db2->result[0]['talk_increment']))?($db2->result[0]['talk_increment']):60;
							$lcr_user_currency = (strlen($db2->result[0]['currency'])?check_str($db2->result[0]['currency']):
								(strlen($_SESSION['billing']['currency']['text'])?$_SESSION['billing']['currency']['text']:'USD')
							);

							// Actually, there is no way to detect what carrier is the calling comming from using current information
							$lcr_rate = 0; $lcr_first_increment = 0; $lcr_second_increment = 0;
							unset($db2->sql);
							unset($db2->result);
							break;
					case "local":
							$destination_number = check_str(urldecode($xml->variables->lcr_query_digits));
							$destination_number_serie = number_series($destination_number);
							$sql_user_rate = "SELECT v_lcr.currency, connect_increment, talk_increment FROM v_lcr JOIN v_billings ON v_billings.type_value='$accountcode' WHERE v_lcr.carrier_uuid IS NULL AND v_lcr.lcr_direction = '".check_str(urldecode($xml->variables->call_direction))."' AND v_lcr.lcr_profile=v_billings.lcr_profile AND NOW() >= v_lcr.date_start AND NOW() < v_lcr.date_end AND digits IN ($destination_number_serie) ORDER BY digits DESC, rate ASC, date_start DESC LIMIT 1";
							if ($debug) {
								echo "sql_user_rate: $sql_user_rate\n";
							}

							$db2->sql = $sql_user_rate;
							$db2->result = $db2->execute();

							// If selling rate is found, then we fill with data, otherwise rate will be 0
							$lcr_currency = (strlen($db2->result[0]['currency'])?check_str($db2->result[0]['currency']):
								(strlen($_SESSION['billing']['currency']['text'])?$_SESSION['billing']['currency']['text']:'USD')
							);
							$lcr_user_rate = (strlen($db2->result[0]['rate']))?($$db2->result[0]['rate']):0;
							$lcr_user_first_increment = (strlen($db2->result[0]['connect_increment']))?($db2->result[0]['connect_increment']):60;
							$lcr_user_second_increment = (strlen($db2->result[0]['talk_increment']))?($db2->result[0]['talk_increment']):60;
							$lcr_user_currency = (strlen($db2->result[0]['currency'])?check_str($db2->result[0]['currency']):
								(strlen($_SESSION['billing']['currency']['text'])?$_SESSION['billing']['currency']['text']:'USD')
							);

							// Actually, internal calls have 0 cost
							$lcr_rate = 0; $lcr_first_increment = 0; $lcr_second_increment = 0;
							unset($db2->sql);
							unset($db2->result);
							break;
				}

				// Please note that we save values using LCR currency, but we discount balance in billing currency

				$time = check_str(urldecode($xml->variables->billsec));
				$call_buy = call_cost($lcr_rate, $lcr_first_increment, $lcr_second_increment, $time);
				$call_sell = call_cost($lcr_user_rate, $lcr_user_first_increment, $lcr_user_second_increment, $time);
				// Costs/Sell call are in original LCR currency, they need to be converted

				$database->fields['call_buy']  = check_str($call_buy);
				$database->fields['call_sell'] = check_str($call_sell);

				$db2->table = "v_xml_cdr";

				$db2->sql = "SELECT currency FROM v_billings WHERE type_value='$accountcode' LIMIT 1";
				$db2->result = $db2->execute();

				$actual_currency = (strlen($lcr_currency)?
								$lcr_currency:
								(strlen($_SESSION['billing']['currency']['text'])?$_SESSION['billing']['currency']['text']:'USD')
				);
				$billing_currency = (strlen($db2->result[0]['currency'])?$db2->result[0]['currency']:$default_currency);

				if ($debug) {
					echo "sql: " . $db2->sql . "\n";
					echo "c ".$database->fields['carrier_name']."\n";
					echo "t $time\n";
					echo "b r:$lcr_rate - $lcr_first_increment - $lcr_first_increment = $call_buy\n";
					echo "s r:$lcr_user_rate - $lcr_user_first_increment - $lcr_user_second_increment = $call_sell\n";
					echo "lcr currency $lcr_currency\n";
					echo "actual currency $actual_currency\n";
					echo "user currency $lcr_user_currency\n";
					echo "billing currency $billing_currency\n";
				}

				unset($database->sql);
				unset($database->result);

				$sql_balance = "SELECT balance, old_balance FROM v_billings WHERE type_value='".check_str(urldecode($xml->variables->accountcode))."'";
				$db2->sql = $sql_balance;
				$db2->result = $db2->execute();
				$balance = $db2->result[0]['balance'];
				$old_balance = $db2->result[0]['old_balance'];

				if ($debug) {
					echo "sql_balance: $sql_balance\n";
					echo "bal: $balance\n";
					echo "old bal: $old_balance\n";
				}

				// Lets convert rate from lcr_currency to billing_currency
				$billing_call_sell = currency_convert($call_sell, $billing_currency, $lcr_user_currency);

				if ($debug) {
					echo "bcs: $billing_call_sell $billing_currency\n";
				}

				// Remember that old_balance is using billing_currency
				$updated_balance = (double)$old_balance - (double)$billing_call_sell;
				unset($db2->sql);
				unset($db2->result);

				$sql_update_balance = "UPDATE v_billings SET balance=$updated_balance, old_balance=$updated_balance WHERE type_value='".check_str(urldecode($xml->variables->accountcode))."'";
				if ($debug) {
					echo "sql_update_balance: $sql_update_balance\n";
				}
				$db2->sql = $sql_update_balance;
				$db2->result = $db2->execute();
				unset($db2->sql);
				unset($db2->result);

			}

		// Custom Callbacks
					
			$custom_callback_enable = isset($_SESSION['cdr']['custom_callback_enable']['boolean']) ? filter_var($_SESSION['cdr']['custom_callback_enable']['boolean'], FILTER_VALIDATE_BOOLEAN) : False;

			if ($custom_callback_enable && strlen($xml->variables->domain_uuid) > 0) {
				$call_back_classes = $_SESSION['cdr']['custom_callback'];

				// Add $call_back_classes with custom domain settings
				$sql = "SELECT domain_setting_value FROM v_domain_settings";
				$sql .= " WHERE domain_uuid = '" . $xml->variables->domain_uuid . "'";
				$sql .= " AND domain_setting_category = 'cdr'";
				$sql .= " AND domain_setting_name = 'array'";
				$sql .= " AND domain_setting_subcategory = 'custom_callback'";
				$sql .= " AND domain_setting_enabled = 'true'";
				$sql .= " ORDER BY domain_setting_order ASC";

				$row = $db->query($sql)->fetchall(PDO::FETCH_NUM);
				foreach ($row as $call_back_class) {
					$call_back_classes[] = $call_back_class[0];
				}

				foreach ($call_back_classes as $call_back_class) {
					if (!class_exists($call_back_class)) {
						continue;
					}
					$callback = new $call_back_class($_SESSION);
					if ($callback->is_ready) {
						$callback->process($xml->variables);
					}
				}
			}

		//insert xml_cdr into the db
			if (strlen($start_stamp) > 0) {
				$database->add();
				if ($debug) {
					echo $database->sql."\n";
				}
			}

		//insert the values
			if (strlen($uuid) > 0) {
				if ($debug) {
					$time5_insert = microtime(true);
					//echo $sql."<br />\n";
				}
				try {
					$error = "false";
					//$db->exec(check_sql($sql));
				}
				catch(PDOException $e) {
					$tmp_dir = $_SESSION['switch']['log']['dir'].'/xml_cdr/failed/';
					if(!file_exists($tmp_dir)) {
						event_socket_mkdir($tmp_dir);
					}
					if ($_SESSION['cdr']['format']['text'] == "xml") {
						$tmp_file = $uuid.'.xml';
						$fh = fopen($tmp_dir.'/'.$tmp_file, 'w');
						fwrite($fh, $xml_string);
					}
					else {
						$tmp_file = $uuid.'.json';
						$fh = fopen($tmp_dir.'/'.$tmp_file, 'w');
						fwrite($fh, json_encode($xml));
					}
					fclose($fh);
					if ($debug) {
						echo $e->getMessage();
					}
					$error = "true";
				}

				if ($_SESSION['cdr']['storage']['text'] == "dir" && $error != "true") {
					if (strlen($uuid) > 0) {
						$tmp_dir = $_SESSION['switch']['log']['dir'].'/xml_cdr/archive/'.$start_year.'/'.$start_month.'/'.$start_day;
						if(!file_exists($tmp_dir)) {
							event_socket_mkdir($tmp_dir);
						}
						if ($_SESSION['cdr']['format']['text'] == "xml") {
							$tmp_file = $uuid.'.xml';
							$fh = fopen($tmp_dir.'/'.$tmp_file, 'w');
							fwrite($fh, $xml_string);
						}
						else {
							$tmp_file = $uuid.'.json';
							$fh = fopen($tmp_dir.'/'.$tmp_file, 'w');
							fwrite($fh, json_encode($xml));
						}
						fclose($fh);
					}
				}
				unset($error);

				if ($debug) {
					GLOBAL $insert_time,$insert_count;
					$insert_time+=microtime(true)-$time5_insert; //add this current query.
					$insert_count++;
				}
			}
			unset($sql);
			
			// Custom Callbacks
			
			$custom_callback_enable = isset($_SESSION['cdr']['custom_callback_enable']['boolean']) ? filter_var($_SESSION['cdr']['custom_callback_enable']['boolean'], FILTER_VALIDATE_BOOLEAN) : False;

			if ($custom_callback_enable && strlen($xml->variables->domain_uuid) > 0) {
				$call_back_classes = $_SESSION['cdr']['custom_callback'];

				// Add $call_back_classes with custom domain settings
				$sql = "SELECT domain_setting_value FROM v_domain_settings";
				$sql .= " WHERE domain_uuid = '" . $xml->variables->domain_uuid . "'";
				$sql .= " AND domain_setting_category = 'cdr'";
				$sql .= " AND domain_setting_name = 'array'";
				$sql .= " AND domain_setting_subcategory = 'custom_callback'";
				$sql .= " AND domain_setting_enabled = 'true'";
				$sql .= " ORDER BY domain_setting_order ASC";

				$row = $db->query($sql)->fetchall(PDO::FETCH_NUM);
				foreach ($row as $call_back_class) {
					$call_back_classes[] = $call_back_class[0];
				}

				foreach ($call_back_classes as $call_back_class) {
					if (!class_exists($call_back_class)) {
						continue;
					}
					$callback = new $call_back_class($_SESSION);
					if ($callback->is_ready) {
						$callback->process($xml->variables);
					}
				}
			}	

	}

//get cdr details from the http post
	if (strlen($_POST["cdr"]) > 0) {
			if ($debug){
				print_r ($_POST["cdr"]);
			}

		//authentication for xml cdr http post
			if ($_SESSION["cdr"]["http_enabled"]["boolean"] == "true" && strlen($_SESSION["xml_cdr"]["username"]) == 0) {
				//get the contents of xml_cdr.conf.xml
					$conf_xml_string = file_get_contents($_SESSION['switch']['conf']['dir'].'/autoload_configs/xml_cdr.conf.xml');

				//parse the xml to get the call detail record info
					try {
						//disable xml entities
						libxml_disable_entity_loader(true);

						//load the string into an xml object
						$conf_xml = simplexml_load_string($conf_xml_string, 'SimpleXMLElement', LIBXML_NOCDATA);
					}
					catch(Exception $e) {
						echo $e->getMessage();
					}
					foreach ($conf_xml->settings->param as $row) {
						if ($row->attributes()->name == "cred") {
							$auth_array = explode(":", $row->attributes()->value);
							//echo "username: ".$auth_array[0]."<br />\n";
							//echo "password: ".$auth_array[1]."<br />\n";
						}
						if ($row->attributes()->name == "url") {
							//check name is equal to url
						}
					}
			}

		//if http enabled is set to false then deny access
			if ($_SESSION["cdr"]["http_enabled"]["boolean"] == "false") {
				echo "access denied<br />\n";
				return;
			}

		//check for the correct username and password
			if ($_SESSION["cdr"]["http_enabled"]["boolean"] == "true") {
				if ($auth_array[0] == $_SERVER["PHP_AUTH_USER"] && $auth_array[1] == $_SERVER["PHP_AUTH_PW"]) {
					//echo "access granted<br />\n";
					$_SESSION["xml_cdr"]["username"] = $auth_array[0];
					$_SESSION["xml_cdr"]["password"] = $auth_array[1];
				}
				else {
					echo "access denied<br />\n";
					return;
				}
			}
		//loop through all attribues
			//foreach($xml->settings->param[1]->attributes() as $a => $b) {
			//		echo $a,'="',$b,"\"<br />\n";
			//}

		//get the http post variable
			$xml_string = trim($_POST["cdr"]);

		//get the leg of the call
			if (substr($_REQUEST['uuid'], 0, 2) == "a_") {
				$leg = "a";
			}
			else {
				$leg = "b";
			}

			xml_cdr_log("process cdr via post\n");
		//parse the xml and insert the data into the db
			process_xml_cdr($db, $leg, $xml_string);
	}

//check the filesystem for xml cdr records that were missed
	$xml_cdr_dir = $_SESSION['switch']['log']['dir'].'/xml_cdr';
	$dir_handle = opendir($xml_cdr_dir);
	$x = 0;
	while($file = readdir($dir_handle)) {
		if ($file != '.' && $file != '..') {
			if ( !is_dir($xml_cdr_dir . '/' . $file) ) {
				//get the leg of the call
					if (substr($file, 0, 2) == "a_") {
						$leg = "a";
					}
					else {
						$leg = "b";
					}

				//get the xml cdr string
					$xml_string = file_get_contents($xml_cdr_dir.'/'.$file);

				//parse the xml and insert the data into the db
					process_xml_cdr($db, $leg, $xml_string);

				//delete the file after it has been imported
					unlink($xml_cdr_dir.'/'.$file);

				//increment the variable
					$x++;
			}
		}
	}
	closedir($dir_handle);

//debug true
	if ($debug) {
		$content = ob_get_contents(); //get the output from the buffer
		ob_end_clean(); //clean the buffer
		$time = "\n\n$insert_count inserts in: ".number_format($insert_time,5). " seconds.\n";
		$time .= "Other processing time: ".number_format((microtime(true)-$time5-$insert_time),5). " seconds.\n";
		xml_cdr_log($content.$time);
	}

?>
