<?php
/* $Id$ */
/*
	click_to_call.php
	Copyright (C) 2008, 2018 Mark J Crane
	All rights reserved.

	Redistribution and use in source and binary forms, with or without
	modification, are permitted provided that the following conditions are met:

	1. Redistributions of source code must retain the above copyright notice,
	   this list of conditions and the following disclaimer.

	2. Redistributions in binary form must reproduce the above copyright
	   notice, this list of conditions and the following disclaimer in the
	   documentation and/or other materials provided with the distribution.

	THIS SOFTWARE IS PROVIDED ``AS IS'' AND ANY EXPRESS OR IMPLIED WARRANTIES,
	INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY
	AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
	AUTHOR BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY,
	OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
	SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
	INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
	CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
	ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
	POSSIBILITY OF SUCH DAMAGE.
	Contributor(s):
	Mark J Crane <markjcrane@fusionpbx.com>
	James Rose <james.o.rose@gmail.com>
*/

//includes
	include "root.php";
	require_once "resources/require.php";
	require_once "resources/check_auth.php";

//check permissions
	if (permission_exists('click_to_call_view') || permission_exists('click_to_call_call')) {
		//access granted
	} else {
		echo "access denied";
		exit;
	}//add multi-lingual support
	$language = new text;
	$text = $language->get();



//send the call
	if (is_array($_GET) && isset($_GET['src']) && isset($_GET['dest'])) {

		$echo_message = "";
		$api_result = array(
			"status" => "500",
			"message" => "",
		);

	//retrieve submitted variables         
		$src = check_str($_GET['src']);         
		$src_cid_name = isset($_GET['src_cid_name']) ? check_str($_GET['src_cid_name']) : "";         
		$src_cid_number = isset($_GET['src_cid_number']) ? check_str($_GET['src_cid_number']) : "";          
		$dest = check_str($_GET['dest']);          
		$auto_answer = isset($_GET['auto_answer']) ? check_str($_GET['auto_answer']) : "false";         
		$rec = check_str($_GET['rec']); //true,false         
		$ringback = isset($_GET['ringback']) ? check_str($_GET['ringback']) : "";
		$context = isset($_GET['context']) ? check_str($_GET['context']) : "";
		$click_to_call_form = check_str($_GET['click_to_call_form']);

		//clean up variable values
		$src = str_replace(array('.','(',')','-',' '), '', $src);
		$dest = (strpbrk($dest, '@') != FALSE) ? str_replace(array('(',')',' '), '', $dest) : str_replace(array('.','(',')','-',' '), '', $dest); //don't strip periods or dashes in sip-uri calls, only phone numbers

		// Update variables from user database if not set explicit
		$domain_name = $_SESSION['domain_name'];

		//create the even socket connection and send the event socket command
		$fp = event_socket_create($_SESSION['event_socket_ip_address'], $_SESSION['event_socket_port'], $_SESSION['event_socket_password']);
		if (!$fp) {
			//error message
			$echo_message .= "<div align='center'><strong>Connection to Event Socket failed.</strong></div>";
			$api_result['status'] = '500';
			$api_result['message'] .= "Connection to Event Socket failed";
		}

		$src = explode("@", $src)[0];

		//mozilla thunderbird TBDialout workaround (seems it can only handle the first %NUM%)
		$dest = ($dest == "%NUM%") ? $src_cid_number : $dest;

		// Check if src and dest are users
		$switch_cmd = "api user_exists id " . $src . " " . $domain_name;
		$src_user_exists = (trim(event_socket_request($fp, $switch_cmd)) == "true") ? True : False;

		$switch_cmd = "api user_exists id " . $dest . " " . $domain_name;
		$dest_user_exists = (trim(event_socket_request($fp, $switch_cmd)) == "true") ? True : False;

		//adjust variable values
		if (strlen($src_cid_name) == 0 && $src_user_exists) {
			$switch_cmd = "api user_data ". $src ."@" . $domain_name . " var";
			$switch_cmd .= $dest_user_exists ? " effective_caller_id_name" : " outbound_caller_id_name";
			$src_cid_name = trim(event_socket_request($fp, $switch_cmd));
			$src_cid_name = (strlen($src_cid_name) > 0 && strpos($src_cid_name, '-ERR') === false) ? $src_cid_name : $src;
		}

		if (strlen($src_cid_number) == 0 && $src_user_exists) {
			$switch_cmd = "api user_data ". $src ."@" . $domain_name . " var";
			$switch_cmd .= $dest_user_exists ? " effective_caller_id_name" : " outbound_caller_id_number";
			$src_cid_number = trim(event_socket_request($fp, $switch_cmd));
			$src_cid_number = (strlen($src_cid_number) > 0 && strpos($src_cid_number, '-ERR') === false) ? $src_cid_number : $src;
		}

		$sip_auto_answer = ($auto_answer == "true") ? ",sip_auto_answer=true" : null;

		// If rec is not set explicit - read user data from Fusion directory
		if (strlen($rec) == 0 && $src_user_exists) {
			$switch_cmd = "api user_data ". $src ."@" . $domain_name . " var user_record";
			$rec_user_setting = trim(event_socket_request($fp, $switch_cmd));
			if ($rec_user_setting == "all" || 
				$rec_user_setting == "outbound" ||
				($rec_user_setting == 'local' && $dest_user_exists)) {
				$rec = 'true';
			}
		}

		$context = (strlen($context) > 0) ? $context : $domain_name;

	//translate ringback
		$ringback_value = "";

		if (strlen($ringback) > 0 && $ringback != 'moh') {
			$switch_cmd = "api eval \$\${" . $ringback . "}";
			$ringback_value = trim(event_socket_request($fp, $switch_cmd));
		}

		if ($ringback_value == "") {
			$switch_cmd = "api user_data ". $src ."@" . $domain_name . " var hold_music";
			$ringback_value = trim(event_socket_request($fp, $switch_cmd));
			$ringback_value = (strlen($ringback_value) > 0 && strpos($ringback_value, '-ERR') === false) ? $ringback_value : "local_stream://default";
		}


	//set call uuid
		$origination_uuid = trim(event_socket_request($fp, "api create_uuid"));

	//add record path and name
		if ($rec == "true") {

			$record_path = $_SESSION['switch']['recordings']['dir']."/".$domain_name."/archive/".date("Y")."/".date("M")."/".date("d");

			$record_extension = isset($_SESSION['recordings']['extension']['text']) ? $_SESSION['recordings']['extension']['text'] : "wav";

			if (isset($_SESSION['recordings']['template']['text'])) {
				//${year}${month}${day}-${caller_id_number}-${caller_destination}-${uuid}.${record_extension}
				$record_name = $_SESSION['recordings']['template']['text'];
				$record_name = str_replace('${year}', date("Y"), $record_name);
				$record_name = str_replace('${month}', date("M"), $record_name);
				$record_name = str_replace('${day}', date("d"), $record_name);
				$record_name = str_replace('${source}', $src, $record_name);
				$record_name = str_replace('${caller_id_name}', $src_cid_name, $record_name);
				$record_name = str_replace('${caller_id_number}', $src_cid_number, $record_name);
				$record_name = str_replace('${caller_destination}', $dest, $record_name);
				$record_name = str_replace('${destination}', $dest, $record_name);
				$record_name = str_replace('${uuid}', $origination_uuid, $record_name);
				$record_name = str_replace('${record_extension}', $record_extension, $record_name);
			}
			else {
				$record_name = $origination_uuid.'.'.$record_extension;
			}
		}

	//determine call direction
		$call_direction = $dest_user_exists ? 'local' : 'outbound';

	//define a leg - set source to display the defined caller id name and number
		$source_common = "{";
		$source_common .= "origination_uuid=" . $origination_uuid;
		$source_common .= ",click_to_call=true";
		$source_common .= ",origination_caller_id_name='" . $dst . "'";
		$source_common .= ",origination_caller_id_number=" . $dst;
		$source_common .= ",instant_ringback=true";
		$source_common .= ",ringback='" . $ringback_value . "'";
		$source_common .= ",presence_id='" . $src . "@" . $domain_name . "'";
		$source_common .= ",call_direction=" . $call_direction;
		if ($rec == "true") {
			$source_common .= ",record_path='" . $record_path . "'";
			$source_common .= ",record_name='" . $record_name . "'";
		}

		if ($src_user_exists) {
			//source is a local extension
			$source = $source_common.$sip_auto_answer.
				",domain_uuid=".$domain_uuid.
				",domain_name=".$domain_name."}user/".$src."@".$domain_name;
		} else {
			//source is an elsewhere number
			$bridge_array = outbound_route_to_bridge($domain_uuid, $src);
			if (!$bridge_array) {
				$api_result['status'] = '404';
				$api_result['message'] = "Cannot dial to source $src";
				$echo_message .= "<div align='center'> <br /><br /><strong> Cannot dial to source " . $src . "</strong></div>\n";
			}
			$source = $source_common."}".$bridge_array[0];
		}
		unset($source_common);

		if (strpbrk($dest, '@') != FALSE) {
			// We have SIP URI
			$dest_command = "&bridge({origination_caller_id_number='". $src_cid_number . "'";
			$dest_command .= ",origination_caller_id_name='" . $src_cid_name . "'";
			$dest_command .= ",ringback='" . $ringback_value . "'";
			$dest_command .= "}sofia/external/" . $dest . ")";
		} else {
			$dest_command = $dest . " XML " . $domain_name . " " . $src_cid_name . " " . $src_cid_number;
		}

	//create the even socket connection and send the event socket command
		if (!$fp) {
			//error message
			$echo_message .= "<div align='center'><strong>Connection to Event Socket failed.</strong></div>";
			$api_result['status'] = '500';
			$api_result['message'] .= "Connection to Event Socket failed.";
		} else {
			//display the last command
			$switch_cmd = "api originate " . $source . " " . $dest_command;
			$echo_message .= "<div align='center'>" . $switch_cmd . "<br /><br /><strong>" . $src . " has called " . $dest . "</strong></div>\n";
			//show the command result
			$result_originate = trim(event_socket_request($fp, $switch_cmd));
			if (substr($result_originate, 0,3) == "+OK") {
				$api_result['status'] = '200';
				$api_result['message'] .= "Call established from " . $src . " to " . $dest;
				//$uuid = substr($result, 4);
				if ($rec == "true") {
					//use the server's time zone to ensure it matches the time zone used by freeswitch
					date_default_timezone_set($_SESSION['time_zone']['system']);
					//create the api record command and send it over event socket
					$switch_cmd = "api uuid_record ".$origination_uuid." start ".$record_path."/".$record_name;
					$result_rec = trim(event_socket_request($fp, $switch_cmd));
				}
			}
			else {
				$api_result['message'] = $result_originate;
			}
			$echo_message .= "<div align='center'><br />".$result_originate."<br /><br /></div>\n";

			if (isset($result_rec)) {
				$echo_message .= "<div align='center'><br />".$result_rec."<br /><br /></div>\n";
			}
		}
		
		// We received a call via API. So, no need to show a form furhter. Actaully we should display API result
		if ($click_to_call_form != 'true') {
			echo json_encode($api_result);
			die;
		}
	}

	if (permission_exists('click_to_call_view')) {
		//access granted
	} else {
		echo "access denied";
		exit;
	}

	// First - show echo message from call request. Mostly - debug message actually
	//include the header
	require_once "resources/header.php";

	if (permission_exists('click_to_call_call')) {
		echo $echo_message;
	}

//show html form
	echo "	<table width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\">\n";
	echo "	<tr>\n";
	echo "	<td align='left'>\n";
	echo "		<span class=\"title\">\n";
	echo "			<strong>".$text['label-click2call']."</strong>\n";
	echo "		</span>\n";
	echo "	</td>\n";
	echo "	<td align='right'>\n";
	echo "		&nbsp;\n";
	echo "	</td>\n";
	echo "	</tr>\n";
	echo "	<tr>\n";
	echo "	<td align='left' colspan='2'>\n";
	echo "		<span class=\"vexpl\">\n";
	echo "			".$text['desc-click2call']."\n";
	echo "		</span>\n";
	echo "	</td>\n";
	echo "\n";
	echo "	</tr>\n";
	echo "	</table>";

	echo "	<br />";

	echo "<form method=\"get\">\n";
	echo "<table border='0' width='100%' cellpadding='0' cellspacing='0'\n";
	echo "<tr>\n";
	echo "	<td class='vncellreq' width='40%'>".$text['label-src-caller-id-nam']."</td>\n";
	echo "	<td class='vtable' align='left'>\n";
	echo "		<input name=\"src_cid_name\" value='".escape($src_cid_name)."' class='formfld'>\n";
	echo "		<br />\n";
	echo "		".$text['desc-src-caller-id-nam']."\n";
	echo "	</td>\n";
	echo "</tr>\n";

	echo "<tr>\n";
	echo "	<td class='vncellreq'>".$text['label-src-caller-id-num']."</td>\n";
	echo "	<td class='vtable' align='left'>\n";
	echo "		<input name=\"src_cid_number\" value='".escape($src_cid_number)."' class='formfld'>\n";
	echo "		<br />\n";
	echo "		".$text['desc-src-caller-id-num']."\n";
	echo "	</td>\n";
	echo "</tr>\n";

	echo "<tr>\n";
	echo "	<td class='vncellreq'>".$text['label-src-num']."</td>\n";
	echo "	<td class='vtable' align='left'>\n";
	echo "		<input name=\"src\" value='$src' class='formfld'>\n";
	echo "		<br />\n";
	echo "		".$text['desc-src-num']."\n";
	echo "	</td>\n";
	echo "</tr>\n";

	echo "<tr>\n";
	echo "	<td class='vncellreq'>".$text['label-dest-num']."</td>\n";
	echo "	<td class='vtable' align='left'>\n";
	echo "		<input name=\"dest\" value='$dest' class='formfld'>\n";
	echo "		<br />\n";
	echo "		".$text['desc-dest-num']."\n";
	echo "	</td>\n";
	echo "</tr>\n";

	echo" <tr>\n";
	echo "<td class='vncell' valign='top' align='left' nowrap='nowrap'>\n";
	echo "	".$text['label-auto-answer']."\n";
	echo "</td>\n";
	echo "<td class='vtable' align='left'>\n";
	echo "    <select class='formfld' name='auto_answer'>\n";
	echo "    <option value=''></option>\n";
	if ($auto_answer == "true") {
			echo "    <option value='true' selected='selected'>".$text['label-true']."</option>\n";
	}
	else {
			echo "    <option value='true'>".$text['label-true']."</option>\n";
	}
	if ($auto_answer == "false") {
			echo "    <option value='false' selected='selected'>".$text['label-false']."</option>\n";
	}
	else {
			echo "    <option value='false'>".$text['label-false']."</option>\n";
	}
	echo "    </select>\n";
	echo "<br />\n";
	echo $text['desc-auto-answer']."\n";
	echo "</td>\n";
	echo "</tr>\n";

	echo "<tr>\n";
	echo "<td class='vncell' valign='top' align='left' nowrap>\n";
	echo "    ".$text['label-record']."\n";
	echo "</td>\n";
	echo "<td class='vtable' align='left'>\n";
	echo "    <select class='formfld' name='rec'>\n";
	echo "    <option value=''></option>\n";
	if ($rec == "true") {
		echo "    <option value='true' selected='selected'>".$text['label-true']."</option>\n";
	}
	else {
		echo "    <option value='true'>".$text['label-true']."</option>\n";
	}
	if ($rec == "false") {
		echo "    <option value='false' selected='selected'>".$text['label-false']."</option>\n";
	}
	else {
		echo "    <option value='false'>".$text['label-false']."</option>\n";
	}
	echo "    </select>\n";
	echo "<br />\n";
	echo $text['desc-record']."\n";
	echo "</td>\n";
	echo "</tr>\n";

	echo "<tr>\n";
	echo "<td class='vncellreq' valign='top' align='left' nowrap>\n";
	echo "    ".$text['label-ringback']."\n";
	echo "</td>\n";
	echo "<td class='vtable' align='left'>\n";
	echo "    <select class='formfld' name='ringback'>\n";
	echo "    <option value=''></option>\n";
	if ($ringback == "us-ring") {
		echo "    <option value='us-ring' selected='selected'>".$text['opt-usring']."</option>\n";
	}
	else {
		echo "    <option value='us-ring'>".$text['opt-usring']."</option>\n";
	}
	if ($ringback == "fr-ring") {
		echo "    <option value='fr-ring' selected='selected'>".$text['opt-frring']."</option>\n";
	}
	else {
		echo "    <option value='fr-ring'>".$text['opt-frring']."</option>\n";
	}
	if ($ringback == "pt-ring") {
		echo "    <option value='pt-ring' selected='selected'>".$text['opt-ptring']."</option>\n";
	}
	else {
		echo "    <option value='pt-ring'>".$text['opt-ptring']."</option>\n";
	}
	if ($ringback == "uk-ring") {
		echo "    <option value='uk-ring' selected='selected'>".$text['opt-ukring']."</option>\n";
	}
	else {
		echo "    <option value='uk-ring'>".$text['opt-ukring']."</option>\n";
	}
	if ($ringback == "rs-ring") {
		echo "    <option value='rs-ring' selected='selected'>".$text['opt-rsring']."</option>\n";
	}
	else {
		echo "    <option value='rs-ring'>".$text['opt-rsring']."</option>\n";
	}
	if ($ringback == "ru-ring") {
		echo "    <option value='ru-ring' selected='selected'>".$text['opt-ruring']."</option>\n";
	}
	else {
		echo "    <option value='ru-ring'>".$text['opt-ruring']."</option>\n";
	}
	if ($ringback == "it-ring") {
		echo "    <option value='it-ring' selected='selected'>".$text['opt-itring']."</option>\n";
	}
	else {
		echo "    <option value='it-ring'>".$text['opt-itring']."</option>\n";
	}
	if ($ringback == "music") {
		echo "    <option value='music' selected='selected'>".$text['opt-moh']."</option>\n";
	}
	else {
		echo "    <option value='music'>".$text['opt-moh']."</option>\n";
	}
	echo "    </select>\n";
	echo "<br />\n";
	echo $text['desc-ringback']."\n";
	echo "</td>\n";
	echo "</tr>\n";

	echo "<tr>\n";
	echo "	<td colspan='2' align='right'>\n";
	echo "		<input type='hidden' name='click_to_call_form' value='true'>\n";
	echo "		<br>";
	echo "		<input type=\"submit\" class='btn' value=\"".$text['button-call']."\">\n";
	echo "	</td>\n";
	echo "</tr>\n";
	echo "</table>\n";
	echo "<br><br>";
	echo "</form>";

//show the footer
	require_once "resources/footer.php";
?>
