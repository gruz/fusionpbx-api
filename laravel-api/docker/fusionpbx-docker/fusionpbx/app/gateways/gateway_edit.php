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
	Portions created by the Initial Developer are Copyright (C) 2008-2019
	the Initial Developer. All Rights Reserved.

	Contributor(s):
	Mark J Crane <markjcrane@fusionpbx.com>
*/

//includes
	require_once "root.php";
	require_once "resources/require.php";
	require_once "resources/check_auth.php";

//check permissions
	if (permission_exists('gateway_add') || permission_exists('gateway_edit')) {
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
	if (isset($_REQUEST["id"]) && is_uuid($_REQUEST["id"])) {
		$action = "update";
		if (isset($_POST["id"])) {
			$gateway_uuid = check_str($_REQUEST["id"]);
		}
		if (isset($_POST["gateway_uuid"])) {
			$gateway_uuid = check_str($_POST["gateway_uuid"]);
		}
	}
	else {
		$action = "add";
		$gateway_uuid = uuid();
	}

//get total gateway count from the database, check limit, if defined
	if ($action == 'add') {
		if ($_SESSION['limit']['gateways']['numeric'] != '') {
			$sql = "select count(gateway_uuid) as num_rows from v_gateways ";
			$sql .= "where ( domain_uuid = '".$_SESSION['domain_uuid']."' ";
			if (permission_exists('gateway_domain')) {
				$sql .= "or domain_uuid is null ";
			}
			$sql .= ");";
			$prep_statement = $db->prepare($sql);
			if ($prep_statement) {
				$prep_statement->execute();
				$row = $prep_statement->fetch(PDO::FETCH_ASSOC);
				$total_gateways = $row['num_rows'];
			}
			unset($prep_statement, $row);
			if ($total_gateways >= $_SESSION['limit']['gateways']['numeric']) {
				messages::add($text['message-maximum_gateways'].' '.$_SESSION['limit']['gateways']['numeric'], 'negative');
				header('Location: gateways.php');
				return;
			}
		}
	}

//get http post variables and set them to php variables
	if (count($_POST) > 0) {
		$domain_uuid = check_str($_POST["domain_uuid"]);
		$gateway = check_str($_POST["gateway"]);
		$username = check_str($_POST["username"]);
		$password = check_str($_POST["password"]);
		$distinct_to = check_str($_POST["distinct_to"]);
		$auth_username = check_str($_POST["auth_username"]);
		$realm = check_str($_POST["realm"]);
		$from_user = check_str($_POST["from_user"]);
		$from_domain = check_str($_POST["from_domain"]);
		$proxy = check_str($_POST["proxy"]);
		$register_proxy = check_str($_POST["register_proxy"]);
		$outbound_proxy = check_str($_POST["outbound_proxy"]);
		$expire_seconds = check_str($_POST["expire_seconds"]);
		$register = check_str($_POST["register"]);
		$register_transport = check_str($_POST["register_transport"]);
		$retry_seconds = check_str($_POST["retry_seconds"]);
		$extension = check_str($_POST["extension"]);
		$ping = check_str($_POST["ping"]);
		$channels = check_str($_POST["channels"]);
		$caller_id_in_from = check_str($_POST["caller_id_in_from"]);
		$supress_cng = check_str($_POST["supress_cng"]);
		$sip_cid_type = check_str($_POST["sip_cid_type"]);
		$codec_prefs = check_str($_POST["codec_prefs"]);
		$extension_in_contact = check_str($_POST["extension_in_contact"]);
		$context = check_str($_POST["context"]);
		$profile = check_str($_POST["profile"]);
		$hostname = check_str($_POST["hostname"]);
		$enabled = check_str($_POST["enabled"]);
		$description = check_str($_POST["description"]);
	}

//prevent the domain_uuid from not being set by someone without this permission
	if (!permission_exists('gateway_domain')) {
		$domain_uuid = $_SESSION['domain_uuid'];
	}

//process the HTTP POST
	if (count($_POST) > 0 && strlen($_POST["persistformvar"]) == 0) {

		//check for all required data
			$msg = '';
			//if (strlen($domain_uuid) == 0) { $msg .= $text['message-required']." ".$text['label-domain_uuid']."<br>\n"; }
			if (strlen($gateway) == 0) { $msg .= $text['message-required']." ".$text['label-gateway']."<br>\n"; }
			if ($register == "true") {
				if (strlen($username) == 0) { $msg .= $text['message-required']." ".$text['label-username']."<br>\n"; }
				if (strlen($password) == 0) { $msg .= $text['message-required']." ".$text['label-password']."<br>\n"; }
			}
			//if (strlen($distinct_to) == 0) { $msg .= $text['message-required']." ".$text['label-distinct_to']."<br>\n"; }
			//if (strlen($auth_username) == 0) { $msg .= $text['message-required']." ".$text['label-auth_username']."<br>\n"; }
			//if (strlen($realm) == 0) { $msg .= $text['message-required']." ".$text['label-realm']."<br>\n"; }
			//if (strlen($from_user) == 0) { $msg .= $text['message-required']." ".$text['label-from_user']."<br>\n"; }
			//if (strlen($from_domain) == 0) { $msg .= $text['message-required']." ".$text['label-from_domain']."<br>\n"; }
			if (strlen($proxy) == 0) { $msg .= $text['message-required']." ".$text['label-proxy']."<br>\n"; }
			//if (strlen($register_proxy) == 0) { $msg .= $text['message-required']." ".$text['label-register_proxy']."<br>\n"; }
			//if (strlen($outbound_proxy) == 0) { $msg .= $text['message-required']." ".$text['label-outbound_proxy']."<br>\n"; }
			if (strlen($expire_seconds) == 0) { $msg .= $text['message-required']." ".$text['label-expire_seconds']."<br>\n"; }
			if (strlen($register) == 0) { $msg .= $text['message-required']." ".$text['label-register']."<br>\n"; }
			//if (strlen($register_transport) == 0) { $msg .= $text['message-required']." ".$text['label-register_transport']."<br>\n"; }
			if (strlen($retry_seconds) == 0) { $msg .= $text['message-required']." ".$text['label-retry_seconds']."<br>\n"; }
			//if (strlen($extension) == 0) { $msg .= $text['message-required']." ".$text['label-extension']."<br>\n"; }
			//if (strlen($ping) == 0) { $msg .= $text['message-required']." ".$text['label-ping']."<br>\n"; }
			if (strlen($channels) == 0) {
				//$msg .= $text['message-required']." ".$text['label-channels']."<br>\n";
				$channels = 0;
			}
			//if (strlen($caller_id_in_from) == 0) { $msg .= $text['message-required']." ".$text['label-caller_id_in_from']."<br>\n"; }
			//if (strlen($supress_cng) == 0) { $msg .= $text['message-required']." ".$text['label-supress_cng']."<br>\n"; }
			//if (strlen($sip_cid_type) == 0) { $msg .= $text['message-required']." ".$text['label-sip_cid_type']."<br>\n"; }
			//if (strlen($codec_prefs) == 0) { $msg .= $text['message-required']." ".$text['label-codec_prefs']."<br>\n"; }
			//if (strlen($extension_in_contact) == 0) { $msg .= $text['message-required']." ".$text['label-extension_in_contact']."<br>\n"; }
			if (strlen($context) == 0) { $msg .= $text['message-required']." ".$text['label-context']."<br>\n"; }
			if (strlen($profile) == 0) { $msg .= $text['message-required']." ".$text['label-profile']."<br>\n"; }
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

				//build the gateway array
					$x = 0;
					if (strlen($domain_uuid) == 0) {
						$array['gateways'][$x]["domain_uuid"] = null;
					}
					else {
						$array['gateways'][$x]["domain_uuid"] = $domain_uuid;
					}
					$array['gateways'][$x]["gateway_uuid"] = $gateway_uuid;
					$array['gateways'][$x]["gateway"] = $gateway;
					$array['gateways'][$x]["username"] = $username;
					$array['gateways'][$x]["password"] = $password;
					$array['gateways'][$x]["distinct_to"] = $distinct_to;
					$array['gateways'][$x]["auth_username"] = $auth_username;
					$array['gateways'][$x]["realm"] = $realm;
					$array['gateways'][$x]["from_user"] = $from_user;
					$array['gateways'][$x]["from_domain"] = $from_domain;
					$array['gateways'][$x]["proxy"] = $proxy;
					$array['gateways'][$x]["register_proxy"] = $register_proxy;
					$array['gateways'][$x]["outbound_proxy"] = $outbound_proxy;
					$array['gateways'][$x]["expire_seconds"] = $expire_seconds;
					$array['gateways'][$x]["register"] = $register;
					$array['gateways'][$x]["register_transport"] = $register_transport;
					$array['gateways'][$x]["retry_seconds"] = $retry_seconds;
					$array['gateways'][$x]["extension"] = $extension;
					$array['gateways'][$x]["ping"] = $ping;
					$array['gateways'][$x]["channels"] = $channels;
					$array['gateways'][$x]["caller_id_in_from"] = $caller_id_in_from;
					$array['gateways'][$x]["supress_cng"] = $supress_cng;
					$array['gateways'][$x]["sip_cid_type"] = $sip_cid_type;
					$array['gateways'][$x]["codec_prefs"] = $codec_prefs;
					$array['gateways'][$x]["extension_in_contact"] = $extension_in_contact;
					$array['gateways'][$x]["context"] = $context;
					$array['gateways'][$x]["profile"] = $profile;
					if (strlen($hostname) == 0) {
						$array['gateways'][$x]["hostname"] = null;
					}
					else {
						$array['gateways'][$x]["hostname"] = $hostname;
					}
					$array['gateways'][$x]["enabled"] = $enabled;
					$array['gateways'][$x]["description"] = $description;

				//update gateway session variable
					if ($enabled == 'true') {
						$_SESSION['gateways'][$gateway_uuid] = $gateway;
					}
					else {
						unset($_SESSION['gateways'][$gateway_uuid]);
					}

				//save to the data
					$database = new database;
					$database->app_name = 'gateways';
					$database->app_uuid = '297ab33e-2c2f-8196-552c-f3567d2caaf8';
					if (strlen($gateway_uuid) > 0) {
						$database->uuid($gateway_uuid);
					}
					$database->save($array);
					$message = $database->message;

				//remove xml file (if any) if not enabled
					if ($enabled != 'true' && $_SESSION['switch']['sip_profiles']['dir'] != '') {
						$gateway_xml_file = $_SESSION['switch']['sip_profiles']['dir']."/".$profile."/v_".$gateway_uuid.".xml";
						if (file_exists($gateway_xml_file)) {
							unlink($gateway_xml_file);
						}
					}

				//syncrhonize configuration
					save_gateway_xml();

				//clear the cache
					$fp = event_socket_create($_SESSION['event_socket_ip_address'], $_SESSION['event_socket_port'], $_SESSION['event_socket_password']);
					$hostname = trim(event_socket_request($fp, 'api switchname'));
					$cache = new cache;
					$cache->delete("configuration:sofia.conf:".$hostname);

				//rescan the external profile to look for new or stopped gateways
					//create the event socket connection
						$fp = event_socket_create($_SESSION['event_socket_ip_address'], $_SESSION['event_socket_port'], $_SESSION['event_socket_password']);
						$tmp_cmd = 'api sofia profile external rescan';
						$response = event_socket_request($fp, $tmp_cmd);
						unset($tmp_cmd);
						usleep(1000);
					//close the connection
						fclose($fp);
					//clear the apply settings reminder
						$_SESSION["reload_xml"] = false;

			} //if ($_POST["persistformvar"] != "true")

		//redirect the user
			if (isset($action)) {
				if ($action == "add") {
					messages::add($text['message-add']);
				}
				if ($action == "update") {
					messages::add($text['message-update']);
				}
				header("Location: gateways.php");
				return;
			}
	} //(count($_POST) > 0 && strlen($_POST["persistformvar"]) == 0)

//pre-populate the form
	if (count($_GET) > 0 && $_POST["persistformvar"] != "true") {
		$gateway_uuid = check_str($_GET["id"]);
		$sql = "select * from v_gateways ";
		$sql .= "where gateway_uuid = '".$gateway_uuid."' ";
		$prep_statement = $db->prepare(check_sql($sql));
		$prep_statement->execute();
		$result = $prep_statement->fetchAll(PDO::FETCH_NAMED);
		foreach ($result as &$row) {
			$domain_uuid = $row["domain_uuid"];
			$gateway = $row["gateway"];
			$username = $row["username"];
			$password = $row["password"];
			$distinct_to = $row["distinct_to"];
			$auth_username = $row["auth_username"];
			$realm = $row["realm"];
			$from_user = $row["from_user"];
			$from_domain = $row["from_domain"];
			$proxy = $row["proxy"];
			$register_proxy = $row["register_proxy"];
			$outbound_proxy = $row["outbound_proxy"];
			$expire_seconds = $row["expire_seconds"];
			$register = $row["register"];
			$register_transport = $row["register_transport"];
			$retry_seconds = $row["retry_seconds"];
			$extension = $row["extension"];
			$ping = $row["ping"];
			$channels = $row["channels"];
			$caller_id_in_from = $row["caller_id_in_from"];
			$supress_cng = $row["supress_cng"];
			$sip_cid_type = $row["sip_cid_type"];
			$codec_prefs = $row["codec_prefs"];
			$extension_in_contact = $row["extension_in_contact"];
			$context = $row["context"];
			$profile = $row["profile"];
			$hostname = $row["hostname"];
			$enabled = $row["enabled"];
			$description = $row["description"];
		}
		unset ($prep_statement);
	}

//get the sip profiles
	$sql = "select sip_profile_name from v_sip_profiles ";
	$sql .= "where sip_profile_enabled = 'true' ";
	$sql .= "order by sip_profile_name asc ";
	$prep_statement = $db->prepare(check_sql($sql));
	$prep_statement->execute();
	$sip_profiles = $prep_statement->fetchAll();
	unset ($prep_statement, $sql);

//set defaults
	if (strlen($enabled) == 0) { $enabled = "true"; }
	if (strlen($register) == 0) { $register = "true"; }
	if (strlen($retry_seconds) == 0) { $retry_seconds = "30"; }

//show the header
	require_once "resources/header.php";

//show the content
	echo "<script type=\"text/javascript\" language=\"JavaScript\">\n";
	echo "\n";
	echo "function enable_change(enable_over) {\n";
	echo "	var endis;\n";
	echo "	endis = !(document.iform.enable.checked || enable_over);\n";
	echo "	document.iform.range_from.disabled = endis;\n";
	echo "	document.iform.range_to.disabled = endis;\n";
	echo "}\n";
	echo "\n";
	echo "function show_advanced_config() {\n";
	echo "	$('#show_advanced_box').slideToggle();\n";
	echo "	$('#show_advanced').slideToggle();\n";
	echo "}\n";
	echo "</script>";

	echo "<form name='frm' id='frm' method='post' action=''>\n";
	echo "<table width='100%' border='0' cellpadding='0' cellspacing='0'>\n";
	echo "<tr>\n";
	echo "<td colspan='2'>\n";

	echo "<table width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\">\n";
	echo "	<tr>\n";
	echo "		<td align='left' width=\"50%\">\n";
	echo "			<span class=\"title\">".$text['title-gateway']."</span><br>\n";
	echo "		</td>";
	echo "		<td width='50%' align='right'>\n";
	echo "			<input type='button' class='btn' name='' alt='".$text['button-back']."' onclick=\"window.location='gateways.php'\" value='".$text['button-back']."'>\n";
	if ($action == "update") {
		echo "			<input type='button' class='btn' name='' alt='".$text['button-copy']."' onclick=\"if (confirm('".$text['confirm-copy']."')){window.location='gateway_copy.php?id=".escape($gateway_uuid)."';}\" value='".$text['button-copy']."'>\n";
	}
	echo "			<input type='button' class='btn' value='".$text['button-save']."' onclick='submit_form();'>\n";
	echo "		</td>\n";
	echo "	</tr>";
	echo "	<tr>";
	echo "		<td align='left' colspan='2'>\n";
	echo "			".$text['description-gateway-edit']."\n";
	echo "		</td>\n";
	echo "	</tr>\n";
	echo "</table>\n";
	echo "<br />\n";

	echo "</td>\n";
	echo "</tr>\n";

	echo "<tr>\n";
	echo "<td width=\"30%\" class='vncellreq' valign='top' align='left' nowrap>\n";
	echo "    ".$text['label-gateway']."\n";
	echo "</td>\n";
	echo "<td width=\"70%\" class='vtable' align='left'>\n";
	echo "    <input class='formfld' type='text' name='gateway' maxlength='255' value=\"".escape($gateway)."\" required='required'>\n";
	echo "<br />\n";
	echo $text['description-gateway-name']."\n";
	echo "</td>\n";
	echo "</tr>\n";

	echo "<tr>\n";
	echo "<td class='vncellreq' valign='top' align='left' nowrap>\n";
	echo "    ".$text['label-username']."\n";
	echo "</td>\n";
	echo "<td class='vtable' align='left'>\n";
	echo "    <input class='formfld' type='text' name='username' maxlength='255' autocomplete='off' value=\"".escape($username)."\">\n";
	echo "<br />\n";
	echo $text['description-username']."\n";
	echo "</td>\n";
	echo "</tr>\n";

	echo "<tr>\n";
	echo "<td class='vncellreq' valign='top' align='left' nowrap>\n";
	echo "    ".$text['label-password']."\n";
	echo "</td>\n";
	echo "<td class='vtable' align='left'>\n";
	echo "    <input class='formfld' type='password' name='password' id='password' autocomplete='off' maxlength='255' onmouseover=\"this.type='text';\" onfocus=\"this.type='text';\" onmouseout=\"if (!$(this).is(':focus')) { this.type='password'; }\" onblur=\"this.type='password';\" value=\"".escape($password)."\">\n";
	echo "    <br />\n";
	echo "    ".$text['description-password']."\n";
	echo "</td>\n";
	echo "</tr>\n";

	echo "<tr>\n";
	echo "<td class='vncell' valign='top' align='left' nowrap>\n";
	echo "    ".$text['label-from_user']."\n";
	echo "</td>\n";
	echo "<td class='vtable' align='left'>\n";
	echo "    <input class='formfld' type='text' name='from_user' maxlength='255' value=\"".escape($from_user)."\">\n";
	echo "<br />\n";
	echo $text['description-from_user']."\n";
	echo "</td>\n";
	echo "</tr>\n";

	echo "<tr>\n";
	echo "<td class='vncell' valign='top' align='left' nowrap>\n";
	echo "    ".$text['label-from_domain']."\n";
	echo "</td>\n";
	echo "<td class='vtable' align='left'>\n";
	echo "    <input class='formfld' type='text' name='from_domain' maxlength='255' value=\"".escape($from_domain)."\">\n";
	echo "<br />\n";
	echo $text['description-from_domain']."\n";
	echo "</td>\n";
	echo "</tr>\n";

	echo "<tr>\n";
	echo "<td class='vncellreq' valign='top' align='left' nowrap>\n";
	echo "    ".$text['label-proxy']."\n";
	echo "</td>\n";
	echo "<td class='vtable' align='left'>\n";
	echo "    <input class='formfld' type='text' name='proxy' maxlength='255' value=\"".escape($proxy)."\" required='required'>\n";
	echo "<br />\n";
	echo $text['description-proxy']."\n";
	echo "</td>\n";
	echo "</tr>\n";

	echo "<tr>\n";
	echo "<td class='vncell' valign='top' align='left' nowrap>\n";
	echo "    ".$text['label-realm']."\n";
	echo "</td>\n";
	echo "<td class='vtable' align='left'>\n";
	echo "    <input class='formfld' type='text' name='realm' maxlength='255' value=\"".escape($realm)."\">\n";
	echo "<br />\n";
	echo $text['description-realm']."\n";
	echo "</td>\n";
	echo "</tr>\n";

	echo "<tr>\n";
	echo "<td class='vncellreq' valign='top' align='left' nowrap>\n";
	echo "    ".$text['label-expire_seconds']."\n";
	echo "</td>\n";
	echo "<td class='vtable' align='left'>\n";
	if (strlen($expire_seconds) == 0) { $expire_seconds = "800"; }
	echo "  <input class='formfld' type='number' name='expire_seconds' maxlength='255' value='".escape($expire_seconds)."' min='1' max='65535' step='1' required='required'>\n";
	echo "<br />\n";
	echo $text['description-expire_seconds']."\n";
	echo "</td>\n";
	echo "</tr>\n";

	echo "<tr>\n";
	echo "<td class='vncellreq' valign='top' align='left' nowrap>\n";
	echo "    ".$text['label-register']."\n";
	echo "</td>\n";
	echo "<td class='vtable' align='left'>\n";
	echo "    <select class='formfld' name='register'>\n";
	if ($register == "true") {
		echo "    <option value='true' selected='selected'>".$text['label-true']."</option>\n";
	}
	else {
		echo "    <option value='true'>".$text['label-true']."</option>\n";
	}
	if ($register == "false") {
		echo "    <option value='false' selected='selected'>".$text['label-false']."</option>\n";
	}
	else {
		echo "    <option value='false'>".$text['label-false']."</option>\n";
	}
	echo "    </select>\n";
	echo "<br />\n";
	echo $text['description-register']."\n";
	echo "</td>\n";
	echo "</tr>\n";

	echo "<tr>\n";
	echo "<td class='vncellreq' valign='top' align='left' nowrap>\n";
	echo "    ".$text['label-retry_seconds']."\n";
	echo "</td>\n";
	echo "<td class='vtable' align='left'>\n";
	echo "  <input class='formfld' type='number' name='retry_seconds' maxlength='255' value='".escape($retry_seconds)."' min='1' max='65535' step='1' required='required'>\n";
	echo "<br />\n";
	echo $text['description-retry_seconds']."\n";
	echo "</td>\n";
	echo "</tr>\n";

	//--- begin: show_advanced -----------------------
	echo "<tr>\n";
	echo "<td style='padding: 0px;' colspan='2' class='' valign='top' align='left' nowrap>\n";

	echo "	<div id=\"show_advanced_box\">\n";
	echo "		<table width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\">\n";
	echo "		<tr>\n";
	echo "		<td width=\"30%\" valign=\"top\" class=\"vncell\">&nbsp;</td>\n";
	echo "		<td width=\"70%\" class=\"vtable\">\n";
	echo "			<input type=\"button\" class=\"btn\" onClick=\"show_advanced_config()\" value=\"".$text['button-advanced']."\"></input>\n";
	echo "		</td>\n";
	echo "		</tr>\n";
	echo "		</table>\n";
	echo "	</div>\n";

	echo "	<div id=\"show_advanced\" style=\"display:none\">\n";
	echo "	<table width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\">\n";

	echo "<tr>\n";
	echo "<td width='30%' class='vncell' valign='top' align='left' nowrap>\n";
	echo "    ".$text['label-distinct_to']."\n";
	echo "</td>\n";
	echo "<td width='70%' class='vtable' align='left'>\n";
	echo "    <select class='formfld' name='distinct_to'>\n";
	echo "    <option value=''></option>\n";
	if ($distinct_to == "true") {
		echo "    <option value='true' selected='selected'>".$text['label-true']."</option>\n";
	}
	else {
		echo "    <option value='true'>".$text['label-true']."</option>\n";
	}
	if ($distinct_to == "false") {
		echo "    <option value='false' selected='selected'>".$text['label-false']."</option>\n";
	}
	else {
		echo "    <option value='false'>".$text['label-false']."</option>\n";
	}
	echo "    </select>\n";
	echo "<br />\n";
	echo $text['description-distinct_to']."\n";
	echo "</td>\n";
	echo "</tr>\n";

	echo "<tr>\n";
	echo "<td width='30%' class='vncell' valign='top' align='left' nowrap>\n";
	echo "    ".$text['label-auth_username']."\n";
	echo "</td>\n";
	echo "<td width='70%' class='vtable' align='left'>\n";
	echo "    <input class='formfld' type='text' name='auth_username' maxlength='255' value=\"".escape($auth_username)."\">\n";
	echo "<br />\n";
	echo $text['description-auth_username']."\n";
	echo "</td>\n";
	echo "</tr>\n";

	echo "<tr>\n";
	echo "<td class='vncell' valign='top' align='left' nowrap>\n";
	echo "    ".$text['label-extension']."\n";
	echo "</td>\n";
	echo "<td class='vtable' align='left'>\n";
	echo "    <input class='formfld' type='text' name='extension' maxlength='255' value=\"".escape($extension)."\">\n";
	echo "<br />\n";
	echo $text['description-extension']."\n";
	echo "</td>\n";
	echo "</tr>\n";

	echo "<tr>\n";
	echo "<td class='vncell' valign='top' align='left' nowrap>\n";
	echo "    ".$text['label-register_transport']."\n";
	echo "</td>\n";
	echo "<td class='vtable' align='left'>\n";
	echo "    <select class='formfld' name='register_transport'>\n";
	echo "    <option value=''></option>\n";
	if ($register_transport == "udp") {
		echo "    <option value='udp' selected='selected'>udp</option>\n";
	}
	else {
		echo "    <option value='udp'>udp</option>\n";
	}
	if ($register_transport == "tcp") {
		echo "    <option value='tcp' selected='selected'>tcp</option>\n";
	}
	else {
		echo "    <option value='tcp'>tcp</option>\n";
	}
	if ($register_transport == "tls") {
		echo "    <option value='tls' selected='selected'>tls</option>\n";
	}
	else {
		echo "    <option value='tls'>tls</option>\n";
	}
	echo "    </select>\n";
	echo "<br />\n";
	echo $text['description-register_transport']."\n";
	echo "</td>\n";
	echo "</tr>\n";

	echo "<tr>\n";
	echo "<td class='vncell' valign='top' align='left' nowrap>\n";
	echo "    ".$text['label-register_proxy']."\n";
	echo "</td>\n";
	echo "<td class='vtable' align='left'>\n";
	echo "    <input class='formfld' type='text' name='register_proxy' maxlength='255' value=\"".escape($register_proxy)."\">\n";
	echo "<br />\n";
	echo $text['description-register_proxy']."\n";
	echo "</td>\n";
	echo "</tr>\n";

	echo "<tr>\n";
	echo "<td class='vncell' valign='top' align='left' nowrap>\n";
	echo "    ".$text['label-outbound_proxy']."\n";
	echo "</td>\n";
	echo "<td class='vtable' align='left'>\n";
	echo "    <input class='formfld' type='text' name='outbound_proxy' maxlength='255' value=\"".escape($outbound_proxy)."\">\n";
	echo "<br />\n";
	echo $text['description-outbound_proxy']."\n";
	echo "</td>\n";
	echo "</tr>\n";

	echo "	<tr>\n";
	echo "	<td class='vncell' valign='top' align='left' nowrap>\n";
	echo "		".$text['label-caller_id_in_from']."\n";
	echo "	</td>\n";
	echo "	<td class='vtable' align='left'>\n";
	echo "		<select class='formfld' name='caller_id_in_from'>\n";
	echo "		<option value=''></option>\n";
	if ($caller_id_in_from == "true" || (strlen($caller_id_in_from) == 0 && $action == 'add')) {
		// When adding, set caller_id_in_from as 'true' by default. On update - show real value.
		echo "		<option value='true' selected='selected'>".$text['label-true']."</option>\n";
	} else {
		echo "		<option value='true'>".$text['label-true']."</option>\n";
	}
	if ($caller_id_in_from == "false") {
		echo "		<option value='false' selected='selected'>".$text['label-false']."</option>\n";
	} else {
		echo "		<option value='false'>".$text['label-false']."</option>\n";
	}
	echo "	</select>\n";
	echo "<br />\n";
	echo $text['description-caller_id_in_from']."\n";
	echo "</td>\n";
	echo "</tr>\n";

	echo "<tr>\n";
	echo "<td class='vncell' valign='top' align='left' nowrap='nowrap'>\n";
	echo "    ".$text['label-supress_cng']."\n";
	echo "</td>\n";
	echo "<td class='vtable' align='left'>\n";
	echo "    <select class='formfld' name='supress_cng'>\n";
	echo "    <option value=''></option>\n";
	if ($supress_cng == "true") {
		echo "    <option value='true' selected='selected'>".$text['label-true']."</option>\n";
	}
	else {
		echo "    <option value='true'>".$text['label-true']."</option>\n";
	}
	if ($supress_cng == "false") {
		echo "    <option value='false' selected='selected'>".$text['label-false']."</option>\n";
	}
	else {
		echo "    <option value='false'>".$text['label-false']."</option>\n";
	}
	echo "    </select>\n";
	echo "<br />\n";
	echo $text['description-supress_cng']."\n";
	echo "</td>\n";
	echo "</tr>\n";

	echo "<tr>\n";
	echo "<td class='vncell' valign='top' align='left' nowrap>\n";
	echo "    ".$text['label-sip_cid_type']."\n";
	echo "</td>\n";
	echo "<td class='vtable' align='left'>\n";
	echo "    <input class='formfld' type='text' name='sip_cid_type' maxlength='255' value=\"".escape($sip_cid_type)."\" pattern='^(none|pid|rpid)$'>\n";
	echo "<br />\n";
	echo $text['description-sip_cid_type']."\n";
	echo "</td>\n";
	echo "</tr>\n";

	echo "<tr>\n";
	echo "<td class='vncell' valign='top' align='left' nowrap>\n";
	echo "    ".$text['label-codec_prefs']."\n";
	echo "</td>\n";
	echo "<td class='vtable' align='left'>\n";
	echo "    <input class='formfld' type='text' name='codec_prefs' maxlength='255' value=\"".escape($codec_prefs)."\">\n";
	echo "<br />\n";
	echo $text['description-codec_prefs']."\n";
	echo "</td>\n";
	echo "</tr>\n";

	echo "<tr>\n";
	echo "<td class='vncell' valign='top' align='left' nowrap>\n";
	echo "    ".$text['label-extension_in_contact']."\n";
	echo "</td>\n";
	echo "<td class='vtable' align='left'>\n";
	echo "    <select class='formfld' name='extension_in_contact'>\n";
	echo "    <option value=''></option>\n";
	if ($extension_in_contact == "true") {
		echo "    <option value='true' selected='selected'>".$text['label-true']."</option>\n";
	}
	else {
		echo "    <option value='true'>".$text['label-true']."</option>\n";
	}
	if ($extension_in_contact == "false") {
		echo "    <option value='false' selected='selected'>".$text['label-false']."</option>\n";
	}
	else {
		echo "    <option value='false'>".$text['label-false']."</option>\n";
	}
	echo "    </select>\n";
	echo "<br />\n";
	echo $text['description-extension_in_contact']."\n";
	echo "</td>\n";
	echo "</tr>\n";

	echo "<tr>\n";
	echo "<td class='vncell' valign='top' align='left' nowrap>\n";
	echo "    ".$text['label-ping']."\n";
	echo "</td>\n";
	echo "<td class='vtable' align='left'>\n";
	echo "    <input class='formfld' type='number' name='ping' maxlength='255' min='1' max='65535' step='1' value=\"".escape($ping)."\">\n";
	echo "<br />\n";
	echo $text['description-ping']."\n";
	echo "</td>\n";
	echo "</tr>\n";

	if (permission_exists('gateway_channels')) {
		echo "<tr>\n";
		echo "<td class='vncell' valign='top' align='left' nowrap>\n";
		echo "    ".$text['label-channels']."\n";
		echo "</td>\n";
		echo "<td class='vtable' align='left'>\n";
		echo "    <input class='formfld' type='number' name='channels' maxlength='255' value=\"".escape($channels)."\" min='0' max='65535' step='1'>\n";
		echo "<br />\n";
		echo $text['description-channels']."\n";
		echo "</td>\n";
		echo "</tr>\n";
	}

	echo "<tr>\n";
	echo "<td class='vncell' valign='top' align='left' nowrap='nowrap'>\n";
	echo "	".$text['label-hostname']."\n";
	echo "</td>\n";
	echo "<td class='vtable' align='left'>\n";
	echo "	<input class='formfld' type='text' name='hostname' maxlength='255' value=\"".escape($hostname)."\">\n";
	echo "<br />\n";
	echo $text['description-hostname']."\n";
	echo "</td>\n";
	echo "</tr>\n";

	if (permission_exists('gateway_domain')) {
		echo "<tr>\n";
		echo "<td class='vncell' valign='top' align='left' nowrap='nowrap'>\n";
		echo "	".$text['label-domain']."\n";
		echo "</td>\n";
		echo "<td class='vtable' align='left'>\n";
		echo "    <select class='formfld' name='domain_uuid'>\n";
		if (strlen($domain_uuid) == 0) {
			echo "    <option value='' selected='selected'>".$text['select-global']."</option>\n";
		}
		else {
			echo "    <option value=''>".$text['select-global']."</option>\n";
		}
		foreach ($_SESSION['domains'] as $row) {
			if ($row['domain_uuid'] == $domain_uuid) {
				echo "    <option value='".escape($row['domain_uuid'])."' selected='selected'>".escape($row['domain_name'])."</option>\n";
			}
			else {
				echo "    <option value='".escape($row['domain_uuid'])."'>".escape($row['domain_name'])."</option>\n";
			}
		}
		echo "    </select>\n";
		echo "<br />\n";
		echo $text['description-domain_name']."\n";
		echo "</td>\n";
		echo "</tr>\n";
	}

	echo "	</table>\n";
	echo "	</div>";

	echo "</td>\n";
	echo "</tr>\n";
	//--- end: show_advanced -----------------------

	echo "<tr>\n";
	echo "<td class='vncell' valign='top' align='left' nowrap='nowrap'>\n";
	echo "	".$text['label-context']."\n";
	echo "</td>\n";
	echo "<td class='vtable' align='left'>\n";
	if (strlen($context) == 0) { $context = "public"; }
	echo "	<input class='formfld' type='text' name='context' maxlength='255' value=\"".escape($context)."\">\n";
	echo "<br />\n";
	echo $text['description-context']."\n";
	echo "</td>\n";
	echo "</tr>\n";

	echo "<tr>\n";
	echo "<td class='vncellreq' valign='top' align='left' nowrap>\n";
	echo "	".$text['label-profile']."\n";
	echo "</td>\n";
	echo "<td class='vtable' align='left'>\n";
	echo "	<select class='formfld' name='profile' required='required'>\n";
	foreach ($sip_profiles as $row) {
		$sip_profile_name = $row["sip_profile_name"];
		if ($profile == $sip_profile_name) {
			echo "	<option value='$sip_profile_name' selected='selected'>".escape($sip_profile_name)."</option>\n";
		}
		else {
			echo "	<option value='".escape($sip_profile_name)."'>".escape($sip_profile_name)."</option>\n";
		}
	}
	echo "	</select>\n";
	echo "<br />\n";
	echo $text['description-profile']."\n";
	echo "</td>\n";
	echo "</tr>\n";

	echo "<tr>\n";
	echo "<td class='vncellreq' valign='top' align='left' nowrap='nowrap'>\n";
	echo "	".$text['label-enabled']."\n";
	echo "</td>\n";
	echo "<td class='vtable' align='left'>\n";
	echo "	<select class='formfld' name='enabled'>\n";
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
	if ($action == "update") {
		echo "		<input type='hidden' name='gateway_uuid' value='".escape($gateway_uuid)."'>\n";
	}
	echo "			<br>";
	echo "			<input type='button' class='btn' value='".$text['button-save']."' onclick='submit_form();'>\n";
	echo "		</td>\n";
	echo "	</tr>";
	echo "</table>";
	echo "<br><br>";
	echo "</form>";

//capture enter key to submit form
	echo "<script>\n";
	echo "	$(window).keypress(function(event){\n";
	echo "		if (event.which == 13) { submit_form(); }\n";
	echo "	});\n";

// convert password fields to
	echo "	function submit_form() {\n";
	echo "		$('input:password').css('visibility','hidden');\n";
	echo "		$('input:password').attr({type:'text'});\n";
	echo "		$('form#frm').submit();\n";
	echo "	}\n";
	echo "</script>\n";

//include the footer
	require_once "resources/footer.php";

?>
