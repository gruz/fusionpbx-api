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

//includes
	require_once "root.php";
	require_once "resources/require.php";
	require_once "resources/check_auth.php";

//check permisions
	if (permission_exists('xml_cdr_view')) {
		//access granted
	}
	else {
		echo "access denied";
		exit;
	}

//add multi-lingual support
	$language = new text;
	$text = $language->get();

//additional includes
	require_once "resources/header.php";
	require_once "resources/paging.php";

//xml cdr include
	$rows_per_page = ($_SESSION['domain']['paging']['numeric'] != '') ? $_SESSION['domain']['paging']['numeric'] : 50;

	$is_join_view = new xml_cdr_join_view($_SESSION['cdr']);

	require_once "xml_cdr_inc.php";

//javascript function: send_cmd
	echo "<script type=\"text/javascript\">\n";
	echo "function send_cmd(url) {\n";
	echo "	if (window.XMLHttpRequest) { // code for IE7+, Firefox, Chrome, Opera, Safari\n";
	echo "		xmlhttp=new XMLHttpRequest();\n";
	echo "	}\n";
	echo "	else {// code for IE6, IE5\n";
	echo "		xmlhttp=new ActiveXObject(\"Microsoft.XMLHTTP\");\n";
	echo "	}\n";
	echo "	xmlhttp.open(\"GET\",url,true);\n";
	echo "	xmlhttp.send(null);\n";
	echo "	document.getElementById('cmd_reponse').innerHTML=xmlhttp.responseText;\n";
	echo "}\n";
	echo "</script>\n";

//javascript to toggle export select box
	echo "<script language='javascript' type='text/javascript'>";
	echo "	var fade_speed = 400;";
	echo "	function toggle_select(select_id) {";
	echo "		$('#'+select_id).fadeToggle(fade_speed, function() {";
	echo "			document.getElementById(select_id).selectedIndex = 0;";
	echo "		});";
	echo "	}";
	echo "</script>";

//page title and description
	echo "<table width='100%' border='0' cellpadding='0' cellspacing='0'>\n";
	echo "<tr>\n";
	echo "<td align='left' nowrap='nowrap' style='vertical-align: top;'><b>".$text['title']."</b><br><br><br></td>\n";
	echo "<td align='right' width='100%' style='vertical-align: top;'>\n";
	echo "	<form id='frm_export' method='post' action='xml_cdr_export.php'>\n";
	echo "	<input type='hidden' name='cdr_id' value='".escape($cdr_id)."'>\n";
	echo "	<input type='hidden' name='direction' value='".escape($direction)."'>\n";
	echo "	<input type='hidden' name='caller_id_name' value='".escape($caller_id_name)."'>\n";
	echo "	<input type='hidden' name='start_stamp_begin' value='".escape($start_stamp_begin)."'>\n";
	echo "	<input type='hidden' name='start_stamp_end' value='".escape($start_stamp_end)."'>\n";
	echo "	<input type='hidden' name='hangup_cause' value='".escape($hangup_cause)."'>\n";
	echo "	<input type='hidden' name='call_result' value='".escape($call_result)."'>\n";
	echo "	<input type='hidden' name='caller_extension_uuid' value='".escape($caller_extension_uuid)."'>\n";
	echo "	<input type='hidden' name='caller_id_number' value='".escape($caller_id_number)."'>\n";
	echo "	<input type='hidden' name='caller_destination' value='".escape($caller_destination)."'>\n";
	echo "	<input type='hidden' name='destination_number' value='".escape($destination_number)."'>\n";
	echo "	<input type='hidden' name='context' value='".escape($context)."'>\n";
	echo "	<input type='hidden' name='answer_stamp_begin' value='".escape($answer_stamp_begin)."'>\n";
	echo "	<input type='hidden' name='answer_stamp_end' value='".escape($answer_stamp_end)."'>\n";
	echo "	<input type='hidden' name='end_stamp_begin' value='".escape($end_stamp_begin)."'>\n";
	echo "	<input type='hidden' name='end_stamp_end' value='".escape($end_stamp_end)."'>\n";
	echo "	<input type='hidden' name='start_epoch' value='".escape($start_epoch)."'>\n";
	echo "	<input type='hidden' name='stop_epoch' value='".escape($stop_epoch)."'>\n";
	echo "	<input type='hidden' name='duration' value='".escape($duration)."'>\n";
	echo "	<input type='hidden' name='billsec' value='".escape($billsec)."'>\n";
	echo "	<input type='hidden' name='xml_cdr_uuid' value='".escape($xml_cdr_uuid)."'>\n";
	echo "	<input type='hidden' name='bleg_uuid' value='".escape($bleg_uuid)."'>\n";
	echo "	<input type='hidden' name='accountcode' value='".escape($accountcode)."'>\n";
	echo "	<input type='hidden' name='read_codec' value='".escape($read_codec)."'>\n";
	echo "	<input type='hidden' name='write_codec' value='".escape($write_codec)."'>\n";
	echo "	<input type='hidden' name='remote_media_ip' value='".escape($remote_media_ip)."'>\n";
	echo "	<input type='hidden' name='network_addr' value='".escape($network_addr)."'>\n";
	echo "	<input type='hidden' name='bridge_uuid' value='".escape($bridge_uuid)."'>\n";
	echo "	<input type='hidden' name='leg' value='".escape($leg)."'>\n";
	if (is_array($_SESSION['cdr']['field'])) {
		foreach ($_SESSION['cdr']['field'] as $field) {
			$array = explode(",", $field);
			$field_name = $array[count($array) - 1];
			if (isset($_REQUEST[$field_name])) {
				echo "	<input type='hidden' name='".escape($field_name)."' value='".escape($$field_name)."'>\n";
			}
		}
	}
	if (isset($order_by)) {
		echo "	<input type='hidden' name='order_by' value='".escape($order_by)."'>\n";
		echo "	<input type='hidden' name='order' value='".escape($order)."'>\n";
	}
	if (permission_exists('xml_cdr_all') && $_REQUEST['show'] == 'all') {
		echo "	<input type='hidden' name='show' value='all'>\n";
	}
	echo "	<table cellpadding='0' cellspacing='0' border='0'>\n";
	echo "		<tr>\n";
	echo "			<td style='vertical-align: top;'>\n";
	if (permission_exists('xml_cdr_all')) {
		if ($_REQUEST['show'] != 'alll') {
			echo "		<input type='button' class='btn' value='".$text['button-show_all']."' onclick=\"window.location='xml_cdr.php?show=all';\">\n";
		}
	}
	if (permission_exists('xml_cdr_search_advanced')) {
		if ($_REQUEST['show'] == 'all') {
			$query_string = "show=all";
		}
		echo "			<input type='button' class='btn' value='".$text['button-advanced_search']."' onclick=\"window.location='xml_cdr_search.php?".escape($query_string)."';\">\n";
	}
	if ($_GET['call_result'] != 'missed') {
		echo "			<input type='button' class='btn' value='".$text['button-missed']."' onclick=\"document.location.href='xml_cdr.php?call_result=missed';\">\n";
	}
	echo "				<input type='button' class='btn' value='".$text['button-statistics']."' onclick=\"document.location.href='xml_cdr_statistics.php';\">\n";
	if (permission_exists('xml_cdr_archive')) {
		if ($_REQUEST['show'] == 'all') {
			$query_string = "show=all";
		}
		echo "			<input type='button' class='btn' value='".$text['button-archive']."' onclick=\"window.location='xml_cdr_archive.php?".escape($query_string)."';\">\n";
	}
	echo "				<input type='button' class='btn' value='".$text['button-export']."' onclick=\"toggle_select('export_format');\">\n";
	echo "				<input type='button' class='btn' value='".$text['button-refresh']."' onclick=\"document.location.href='xml_cdr.php';\" />\n";
	echo "			</td>";
	echo "			<td style='vertical-align: top;'>";
	echo "				<select class='formfld' style='display: none; width: auto; margin-left: 3px;' name='export_format' id='export_format' onchange=\"display_message('".$text['message-preparing_download']."'); toggle_select('export_format'); document.getElementById('frm_export').submit();\">\n";
	echo "					<option value=''>...</option>\n";
	echo "					<option value='csv'>CSV</option>\n";
	echo "					<option value='pdf'>PDF</option>\n";
	echo "				</select>\n";
	echo "			</td>\n";
	echo "			<td style='vertical-align: top; padding-left: 15px;'>".$paging_controls_mini."</td>\n";
	echo "		</tr>\n";
	echo "	</table>\n";
	echo "	</form>\n";
	echo "</td>\n";
	echo "</tr>\n";

	echo "<tr>\n";
	echo "<td align='left' colspan='2'>\n";

	echo $text['description']." \n";
	echo $text['description2']." \n";
	echo $text['description-3']." \n";
	echo $text['description-4']." \n";

	echo "<br /><br />\n";

	echo "</td>\n";
	echo "</tr>\n";
	echo "</table>\n";

	//basic search of call detail records
		if (permission_exists('xml_cdr_search')) {

			echo "<form method='get' action=''>\n";

			echo "<table width='100%' border='0' cellpadding='0' cellspacing='0'>\n";
			echo "<tr>\n";
			echo "<td width='".((if_group("admin") || if_group("superadmin") || if_group("cdr")) ? '19%' : '30%')."' style='vertical-align: top;'>\n";

				echo "<table width='100%' border='0' cellpadding='0' cellspacing='0'>\n";
				echo "	<tr>\n";
				echo "		<td class='vncell' valign='top' nowrap='nowrap'>\n";
				echo "			".$text['label-direction']."\n";
				echo "		</td>\n";
				echo "		<td class='vtable' align='left'>\n";
				echo "			<select name='direction' class='formfld'>\n";
				echo "				<option value=''></option>\n";
				echo "				<option value='inbound' ".(($direction == "inbound") ? "selected='selected'" : null).">".$text['label-inbound']."</option>\n";
				echo "				<option value='outbound' ".(($direction == "outbound") ? "selected='selected'" : null).">".$text['label-outbound']."</option>\n";
				echo "				<option value='local' ".(($direction == "local") ? "selected='selected'" : null).">".$text['label-local']."</option>\n";
				echo "			</select>\n";
				echo "		</td>\n";
				echo "	</tr>\n";

				echo "	<tr>\n";
				echo "		<td class='vncell' valign='top' nowrap='nowrap'>\n";
				echo "			".$text['label-status']."\n";
				echo "		</td>\n";
				echo "		<td class='vtable' align='left'>\n";
				echo "			<select name='call_result' class='formfld'>\n";
				echo "				<option value=''></option>\n";
				echo "				<option value='answered' ".(($call_result == 'answered') ? 'selected' : null).">".$text['label-answered']."</option>\n";
				echo "				<option value='missed' ".(($call_result == 'missed') ? 'selected' : null).">".$text['label-missed']."</option>\n";
				echo "				<option value='voicemail' ".(($call_result == 'voicemail') ? 'selected' : null).">".$text['label-voicemail']."</option>\n";
				echo "				<option value='cancelled' ".(($call_result == 'cancelled') ? 'selected' : null).">".$text['label-cancelled']."</option>\n";
				echo "				<option value='failed' ".(($call_result == 'failed') ? 'selected' : null).">".$text['label-failed']."</option>\n";
				echo "			</select>\n";
				echo "		</td>\n";
				echo "	</tr>\n";
				echo "</table>\n";

			echo "</td>";
			echo "<td width='".((if_group("admin") || if_group("superadmin") || if_group("cdr")) ? '24%' : '30%')."' style='vertical-align: top;'>\n";

				echo "<table width='100%' border='0' cellpadding='0' cellspacing='0'>\n";
				echo "	<tr>\n";
				echo "		<td class='vncell' valign='top' nowrap='nowrap'>\n";
				echo "			".$text['label-caller_id_number']."\n";
				echo "		</td>\n";
				echo "		<td class='vtable' align='left' style='white-space: nowrap;'>\n";
				echo "			<input type='text' class='formfld' style='".escape($style['caller_id_number'])."' name='caller_id_number' id='caller_id_number' value='".escape($caller_id_number)."'>\n";
				echo "		</td>\n";
				echo "	</tr>\n";
				echo "	<tr>\n";
				echo "		<td class='vncell' valign='top' nowrap='nowrap'>\n";
				echo "			".$text['label-destination']."\n";
				echo "		</td>\n";
				echo "		<td class='vtable' align='left' style='white-space: nowrap;'>\n";
				echo "			<input type='text' class='formfld' name='destination_number' id='destination_number' value='".escape($destination_number)."'>\n";
				echo "		</td>\n";
				echo "	</tr>\n";
				echo "</table>\n";

			echo "</td>";
			echo "<td width='".((if_group("admin") || if_group("superadmin") || if_group("cdr")) ? '30%' : '40%')."' style='vertical-align: top;'>\n";

				echo "<table width='100%' border='0' cellpadding='0' cellspacing='0'>\n";
				echo "	<tr>\n";
				echo "		<td class='vncell' valign='top' nowrap='nowrap'>\n";
				echo "			".$text['label-start_range']."\n";
				echo "		</td>\n";
				echo "		<td class='vtable' align='left' style='position: relative; min-width: 250px;'>\n";
				echo "			<input type='text' class='formfld datetimepicker' style='min-width: 115px; width: 115px;' name='start_stamp_begin' placeholder='".$text['label-from']."' value='".escape($start_stamp_begin)."'>\n";
				echo "			<input type='text' class='formfld datetimepicker' style='min-width: 115px; width: 115px;' name='start_stamp_end' placeholder='".$text['label-to']."' value='".escape($start_stamp_end)."'>\n";
				echo "		</td>\n";
				echo "	</tr>\n";
				echo "	<tr>\n";
				echo "		<td class='vncell' valign='top' nowrap='nowrap'>\n";
				echo "			".$text['label-caller_id_name']."\n";
				echo "		</td>\n";
				echo "		<td class='vtable' align='left'>\n";
				echo "			<input type='text' class='formfld' name='caller_id_name' value='".escape($caller_id_name)."'>\n";
				echo "		</td>\n";
				echo "	</tr>\n";
				echo "</table>\n";

			echo "</td>";

			// show hangup clause filter to super/admin
			echo "<td width='27%' style='vertical-align: top;'>\n";

				echo "<table width='100%' border='0' cellpadding='0' cellspacing='0'>\n";
				echo "	<tr>\n";
				if (permission_exists('hangup_cause')) {
					echo "		<td class='vncell' valign='top' nowrap='nowrap'>\n";
					echo "			".$text['label-hangup_cause']."\n";
					echo "		</td>\n";
					echo "		<td class='vtable' align='left'>\n";
					echo "			<select name='hangup_cause' class='formfld'>\n";
					echo "				<option value=''></option>\n";

					$cdr_status_options = array(
						'NORMAL_CLEARING',
						'ORIGINATOR_CANCEL',
						'BLIND_TRANSFER',
						'LOSE_RACE',
						'NO_ANSWER',
						'NORMAL_UNSPECIFIED',
						'NO_USER_RESPONSE',
						'NO_ROUTE_DESTINATION',
						'SUBSCRIBER_ABSENT',
						'NORMAL_TEMPORARY_FAILURE',
						'ATTENDED_TRANSFER',
						'PICKED_OFF',
						'USER_BUSY',
						'CALL_REJECTED',
						'INVALID_NUMBER_FORMAT',
						'NETWORK_OUT_OF_ORDER',
						'DESTINATION_OUT_OF_ORDER',
						'RECOVERY_ON_TIMER_EXPIRE',
						'MANAGER_REQUEST',
						'MEDIA_TIMEOUT',
						'UNALLOCATED_NUMBER',
						'NONE',
						'EXCHANGE_ROUTING_ERROR',
						'ALLOTTED_TIMEOUT',
						'CHAN_NOT_IMPLEMENTED',
						'INCOMPATIBLE_DESTINATION',
						'USER_NOT_REGISTERED',
						'SYSTEM_SHUTDOWN',
						'MANDATORY_IE_MISSING',
						'BEARERCAPABILITY_NOTAUTH',
						'REQUESTED_CHAN_UNAVAIL'
						);
					sort($cdr_status_options);
					foreach ($cdr_status_options as $cdr_status) {
						$selected = ($hangup_cause == $cdr_status) ? "selected='selected'" : null;
						$cdr_status_label = ucwords(strtolower(str_replace("_", " ", $cdr_status)));
						echo "			<option value='".escape($cdr_status)."' ".escape($selected).">".escape($cdr_status_label)."</option>\n";
					}
					echo "			</select>\n";
					echo "		</td>\n";
					echo "	</tr>\n";
				}
				if (permission_exists('caller_destination')) {
					echo "	<tr>\n";
					echo "		<td class='vncell' valign='top' nowrap='nowrap'>\n";
					echo "			".$text['label-caller_destination']."\n";
					echo "		</td>\n";
					echo "		<td class='vtable' align='left'>\n";
					echo "			<input type='text' class='formfld' name='caller_destination' value='".escape($caller_destination)."'>\n";
					echo "		</td>\n";
					echo "	</tr>\n";
				}
				echo "</table>\n";

			echo "</td>";

			echo "</tr>";
			echo "</table>";

			echo "<table width='100%' border='0' cellpadding='0' cellspacing='0'>\n";
			echo "<tr>";
			echo "<td style='padding-top: 8px;' align='left'>";
			echo 	$text['description_search'];
			echo "</td>";
			echo "<td style='padding-top: 8px;' align='right' nowrap>";
			if (permission_exists('xml_cdr_all') && $_REQUEST['show'] == 'all') {
				echo "<input type='hidden' name='show' value='all'>\n";
			}
			echo "<input type='button' class='btn' value='".$text['button-reset']."' onclick=\"document.location.href='xml_cdr.php';\">\n";
			echo "<input type='submit' class='btn' name='submit' value='".$text['button-search']."'>\n";

			echo "</td>";
			echo "</tr>";
			echo "</table>";

			echo "</form>";
			echo "<br /><br />";
		}

//mod paging parameters for inclusion in column sort heading links
	$param = substr($param, 1); //remove leading '&'
	$param = substr($param, 0, strrpos($param, '&order_by=')); //remove trailing order by

//show the results
	$col_count = 8;
	echo "<form name='frm' method='post' action='xml_cdr_delete.php'>\n";
	echo "<table class='tr_hover' width='100%' cellpadding='0' cellspacing='0' border='0'>\n";
	echo "<tr>\n";
	if (permission_exists('xml_cdr_delete') && $result_count > 0) {
		echo "<th style='width: 30px; text-align: center; padding: 0px;'><input type='checkbox' id='chk_all' onchange=\"(this.checked) ? check('all') : check('none');\"></th>";
		$col_count++;
	}
	//column headings
		echo "<th>&nbsp;</th>\n";
		if (permission_exists('xml_cdr_extension')) {
			echo th_order_by('extension', $text['label-extension'], $order_by, $order, null, null, $param);
		}
		if ($_REQUEST['show'] == "all" && permission_exists('xml_cdr_all')) {
			echo th_order_by('domain_name', $text['label-domain'], $order_by, $order, null, null, $param);
			$col_count++;
		}
		echo th_order_by('caller_id_name', $text['label-caller_id_name'], $order_by, $order, null, null, $param);
		echo th_order_by('caller_id_number', $text['label-caller_id_number'], $order_by, $order, null, null, $param);
		if (permission_exists('caller_destination')) {
			echo th_order_by('caller_destination', $text['label-caller_destination'], $order_by, $order, null, null, $param);
		}
		echo th_order_by('destination_number', $text['label-destination'], $order_by, $order, null, null, $param);
		if (permission_exists('recording_play') || permission_exists('recording_download')) {
			echo "<th>".$text['label-recording']."</th>\n";
			$col_count++;
		}
		if (is_array($_SESSION['cdr']['field'])) {
			foreach ($_SESSION['cdr']['field'] as $field) {
				$array = explode(",", $field);
				$field_name = $array[count($array) - 1];
				$field_label = ucwords(str_replace("_", " ", $field_name));
				$field_label = str_replace("Sip", "SIP", $field_label);
				if ($field_name != "destination_number") {
					echo th_order_by($field_name, $field_label, $order_by, $order, null, "style='text-align: right;'", $param);
				}
			}
		}
		echo th_order_by('start_stamp', $text['label-start'], $order_by, $order, null, "style='text-align: center;'", $param);
		echo th_order_by('tta', $text['label-tta'], $order_by, $order, null, "style='text-align: right;'", $param, $text['description-tta']);
		echo th_order_by('duration', $text['label-duration'], $order_by, $order, null, "style='text-align: center;'", $param);
		if (file_exists($_SERVER["PROJECT_ROOT"]."/app/billing/app_config.php")){
			echo "<th>".$text['label-price']."</th>\n";
			$col_count++;
		}
		if (permission_exists('xml_cdr_pdd')) {
			echo th_order_by('pdd_ms', $text['label-pdd'], $order_by, $order, null, "style='text-align: right;'", $param, $text['description-pdd']);
			$col_count++;
		}
		if (permission_exists('xml_cdr_mos')) {
			echo th_order_by('rtp_audio_in_mos', $text['label-mos'], $order_by, $order, null, "style='text-align: center;'", $param, $text['description-mos']);
			$col_count++;
		}
		if (permission_exists('hangup_cause')) {
			echo th_order_by('hangup_cause', $text['label-hangup_cause'], $order_by, $order, null, null, $param);
		}
		else {
			echo "<th>".$text['label-status']."</th>\n";
		}
		if (if_group("admin") || if_group("superadmin") || if_group("cdr")) {
			echo "<td class='list_control_icon'>";
			if (permission_exists('xml_cdr_delete') && $result_count > 0) {
				echo "<a href='javascript:void(0);' onclick=\"if (confirm('".$text['confirm-delete']."')) { document.forms.frm.submit(); }\" alt='".$text['button-delete']."'>".$v_link_label_delete."</a>";
			}
			echo "</td>\n";
			$col_count++;
		}
		echo "</tr>\n";

	if (file_exists($_SERVER["PROJECT_ROOT"]."/app/billing/app_config.php")){
		require_once "app/billing/resources/functions/rating.php";
		require_once "resources/classes/database.php";
		$database = new database;
	}

	if ($result_count > 0) {
		//determine if theme images exist
			$theme_image_path = $_SERVER["DOCUMENT_ROOT"]."/themes/".$_SESSION['domain']['template']['name']."/images/";
			$theme_cdr_images_exist = (
				file_exists($theme_image_path."icon_cdr_inbound_answered.png") &&
				file_exists($theme_image_path."icon_cdr_inbound_voicemail.png") &&
				file_exists($theme_image_path."icon_cdr_inbound_cancelled.png") &&
				file_exists($theme_image_path."icon_cdr_inbound_failed.png") &&
				file_exists($theme_image_path."icon_cdr_outbound_answered.png") &&
				file_exists($theme_image_path."icon_cdr_outbound_cancelled.png") &&
				file_exists($theme_image_path."icon_cdr_outbound_failed.png") &&
				file_exists($theme_image_path."icon_cdr_local_answered.png") &&
				file_exists($theme_image_path."icon_cdr_local_voicemail.png") &&
				file_exists($theme_image_path."icon_cdr_local_cancelled.png") &&
				file_exists($theme_image_path."icon_cdr_local_failed.png")
				) ? true : false;

		// Cleanup call results

		$xml_cdr_total_hidden = 0;

		if ($is_join_view->status()) {
			$is_join_view->cleanup($result);
		}

		// Show actual results
		foreach($result as $index => $row) {

			// Show actual as bold
			$tr_style = "";
			if ($is_join_view->status()) {
				if (isset($row['hidden'])) {
					$xml_cdr_total_hidden += 1;
				} else {
					$tr_style = " style=font-weight:bold";
				}
			}

			//get the date and time
				$tmp_year = date("Y", strtotime($row['start_stamp']));
				$tmp_month = date("M", strtotime($row['start_stamp']));
				$tmp_day = date("d", strtotime($row['start_stamp']));
				$tmp_start_epoch = ($_SESSION['domain']['time_format']['text'] == '12h') ? date("j M Y g:i:sa", $row['start_epoch']) : date("j M Y H:i:s", $row['start_epoch']);

			//get the hangup cause
				$hangup_cause = $row['hangup_cause'];
				$hangup_cause = str_replace("_", " ", $hangup_cause);
				$hangup_cause = strtolower($hangup_cause);
				$hangup_cause = ucwords($hangup_cause);

			//if call cancelled, show the ring time, not the bill time.
				$seconds = ($row['hangup_cause']=="ORIGINATOR_CANCEL") ? $row['duration'] : round(($row['billmsec'] / 1000), 0, PHP_ROUND_HALF_UP);

			//determine recording properties.
				if (permission_exists('recording_play') || permission_exists('recording_download')) {
					$record_path = $row['record_path'];
					$record_name = $row['record_name'];
					//$record_name = strtolower(pathinfo($tmp_name, PATHINFO_BASENAME));
					$record_extension = pathinfo($record_name, PATHINFO_EXTENSION);
					switch ($record_extension) {
						case "wav" : $record_type = "audio/wav"; break;
						case "mp3" : $record_type = "audio/mpeg"; break;
						case "ogg" : $record_type = "audio/ogg"; break;
					}
				}

			//recording playback
				if ($seconds > 0 && permission_exists('recording_play') && $record_path != '') {
					echo "<tr id='recording_progress_bar_".$row['xml_cdr_uuid']."' style='display: none;'><td class='".$row_style[$c]." playback_progress_bar_background' style='padding: 0; border: none;' colspan='".$col_count."'><span class='playback_progress_bar' id='recording_progress_".$row['xml_cdr_uuid']."'></span></td></tr>\n";
				}

				if ($row['raw_data_exists'] && permission_exists('xml_cdr_details')) {
					$tr_link = "href='xml_cdr_details.php?id=".escape($row['xml_cdr_uuid']).(($_REQUEST['show']) ? "&show=all" : null)."'";
				}
				else {
					$tr_link = "";
				}
				echo "<tr " . $tr_link . $tr_style . ">\n";
				if (permission_exists('xml_cdr_delete')) {
					echo "	<td valign='top' class='".$row_style[$c]." tr_link_void' style='text-align: center; vertical-align: middle; padding: 0px;'>";
					echo "		<input type='checkbox' name='id[".$index."]' id='checkbox_".escape($row['xml_cdr_uuid'])."' value='".escape($row['xml_cdr_uuid'])."' onclick=\"if (this.checked) { document.getElementById('recording_".escape($row['xml_cdr_uuid'])."').value='".base64_encode(escape($record_path).'/'.escape($record_name))."' } else { document.getElementById('recording_".escape($row['xml_cdr_uuid'])."').value=''; document.getElementById('chk_all').checked = false; }\">";
					echo "		<input type='hidden' name='rec[".$index."]' id='recording_".escape($row['xml_cdr_uuid'])."'>";
					echo "	</td>";
					$xml_ids[] = 'checkbox_'.$row['xml_cdr_uuid'];
				}

			//determine call result and appropriate icon
				echo "<td valign='top' class='".$row_style[$c]."'>\n";
				if ($theme_cdr_images_exist) {
					if ($row['direction'] == 'inbound' || $row['direction'] == 'local') {
						if ($row['answer_stamp'] != '' && $row['bridge_uuid'] != '') { $call_result = 'answered'; }
						else if ($row['answer_stamp'] != '' && $row['bridge_uuid'] == '') { $call_result = 'voicemail'; }
						else if ($row['answer_stamp'] == '' && $row['bridge_uuid'] == '' && $row['sip_hangup_disposition'] != 'send_refuse') { $call_result = 'cancelled'; }
						else { $call_result = 'failed'; }
					}
					else if ($row['direction'] == 'outbound') {
						if ($row['answer_stamp'] != '' && $row['bridge_uuid'] != '') { $call_result = 'answered'; }
						else if ($row['answer_stamp'] == '' && $row['bridge_uuid'] != '') { $call_result = 'cancelled'; }
						else { $call_result = 'failed'; }
					}
					if (strlen($row['direction']) > 0) {
						$image_name = "icon_cdr_" . $row['direction'] . "_" . $call_result;
						if($row['leg'] == 'b'){
							$image_name .= '_b';
						}
						$image_name .= ".png";
						echo "<img src='".PROJECT_PATH."/themes/".$_SESSION['domain']['template']['name']."/images/".escape($image_name)."' width='16' style='border: none; cursor: help;' title='".$text['label-'.$row['direction']].": ".$text['label-'.$call_result]. ($row['leg']=='b'?'(b)':'') . "'>\n";
					}
				}
				else { 
					echo "&nbsp;"; 
				}

				echo "</td>\n";
			//extension
				if (permission_exists('xml_cdr_extension')) {
					echo "	<td valign='top' class='".$row_style[$c]."'>";
					echo 	$row['extension'].'&nbsp;';
					echo "	</td>\n";
				}
			//domain name
				if ($_REQUEST['show'] == "all" && permission_exists('xml_cdr_all')) {
					echo "	<td valign='top' class='".$row_style[$c]."'>";
					echo 	$row['domain_name'].'&nbsp;';
					echo "	</td>\n";
				}
			//caller id name
				echo "	<td valign='top' class='".$row_style[$c]."'>".escape(substr($row['caller_id_name'], 0, 20))."&nbsp;</td>\n";
			//source
				echo "	<td valign='top' class='".$row_style[$c]." tr_link_void' nowrap='nowrap'>";
				echo "		<a href=\"javascript:void(0)\" onclick=\"send_cmd('".PROJECT_PATH."/app/click_to_call/click_to_call.php?src_cid_name=".urlencode(escape($row['caller_id_name']))."&src_cid_number=".urlencode(escape($row['caller_id_number']))."&dest_cid_name=".urlencode($_SESSION['user']['extension'][0]['outbound_caller_id_name'])."&dest_cid_number=".urlencode($_SESSION['user']['extension'][0]['outbound_caller_id_number'])."&src=".urlencode($_SESSION['user']['extension'][0]['user'])."&dest=".urlencode(escape($row['caller_id_number']))."&rec=false&ringback=us-ring&auto_answer=true');\">\n";
				if (is_numeric($row['caller_id_number'])) {
					echo "		".format_phone(substr($row['caller_id_number'], 0, 20)).' ';
				}
				else {
					echo "		".escape(substr($row['caller_id_number'], 0, 20)).' ';
				}
				echo "		</a>";
				echo "	</td>\n";
			//caller destination
				if (permission_exists('caller_destination')) {
					echo "	<td valign='top' class='".$row_style[$c]." tr_link_void' nowrap='nowrap'>";
					echo "		<a href=\"javascript:void(0)\" onclick=\"send_cmd('".PROJECT_PATH."/app/click_to_call/click_to_call.php?src_cid_name=".urlencode(escape($row['caller_id_name']))."&src_cid_number=".urlencode(escape($row['caller_id_number']))."&dest_cid_name=".urlencode($_SESSION['user']['extension'][0]['outbound_caller_id_name'])."&dest_cid_number=".urlencode($_SESSION['user']['extension'][0]['outbound_caller_id_number'])."&src=".urlencode($_SESSION['user']['extension'][0]['user'])."&dest=".urlencode(escape($row['caller_destination']))."&rec=false&ringback=us-ring&auto_answer=true');\">\n";
					if (is_numeric($row['caller_destination'])) {
						echo "		".format_phone(escape(substr($row['caller_destination'], 0, 20))).' ';
					}
					else {
						echo "		".escape(substr($row['caller_destination'], 0, 20)).' ';
					}
					echo "		</a>";
					echo "	</td>\n";
				}
			//destination
			
				echo "	<td valign='top' class='".$row_style[$c]." tr_link_void' nowrap='nowrap'>";
				echo "		<a href=\"javascript:void(0)\" onclick=\"send_cmd('".PROJECT_PATH."/app/click_to_call/click_to_call.php?src_cid_name=".urlencode(escape($row['destination_number']))."&src_cid_number=".urlencode(escape($row['destination_number']))."&dest_cid_name=".urlencode($_SESSION['user']['extension'][0]['outbound_caller_id_name'])."&dest_cid_number=".urlencode($_SESSION['user']['extension'][0]['outbound_caller_id_number'])."&src=".urlencode($_SESSION['user']['extension'][0]['user'])."&dest=".urlencode(escape($row['destination_number']))."&rec=false&ringback=us-ring&auto_answer=true');\">\n";
				if (is_numeric($row['destination_number'])) {
					echo format_phone(escape(substr($row['destination_number'], 0, 20)))."\n";
				}
				else {
					echo "		".escape(substr($row['destination_number'], 0, 20))."\n";
				}
				echo "		</a>\n";
				echo "	</td>\n";
			//recording
				if (permission_exists('recording_play') || permission_exists('recording_download')) {
					if ($seconds > 0 && $record_path != '' && file_exists($record_path.'/'.$record_name)) {
						echo "	<td valign='top' align='center' class='".$row_style[$c]." row_style_slim tr_link_void' nowrap='nowrap'>";
						if (permission_exists('recording_play')) {
							echo 	"<audio id='recording_audio_".escape($row['xml_cdr_uuid'])."' style='display: none;' preload='none' ontimeupdate=\"update_progress('".escape($row['xml_cdr_uuid'])."')\" onended=\"recording_reset('".escape($row['xml_cdr_uuid'])."');\" src=\"download.php?id=".escape($row['xml_cdr_uuid'])."&t=record\" type='".escape($record_type)."'></audio>";
							echo 	"<span id='recording_button_".escape($row['xml_cdr_uuid'])."' onclick=\"recording_play('".escape($row['xml_cdr_uuid'])."')\" title='".$text['label-play']." / ".$text['label-pause']."'>".$v_link_label_play."</span>";
						}
						else {
							echo "don't have recording_play permission ";
						}
						if (permission_exists('recording_download')) {
							echo 	"<a href=\"download.php?id=".escape($row['xml_cdr_uuid'])."&t=bin\" title='".$text['label-download']."'>".$v_link_label_download."</a>";
						}
						echo "	</td>\n";
					}
					else {
						echo "	<td valign='top' align='center' class='".$row_style[$c]."'>&nbsp;</td>\n";
					}
				}
			//dynamic cdr fields
				if (is_array($_SESSION['cdr']['field'])) {
					foreach ($_SESSION['cdr']['field'] as $field) {
						$array = explode(",", $field);
						$field_name = $array[count($array) - 1];
						if ($field_name != "destination_number") {
							echo "	<td valign='top' class='".$row_style[$c]."' style='text-align: center;' nowrap='nowrap'>".escape($row[$field_name])."</td>\n";
						}
					}
				}
			//start
				echo "	<td valign='top' class='".$row_style[$c]."' style='text-align: center;' nowrap='nowrap'>".escape($tmp_start_epoch)."</td>\n";
			//tta (time to answer)
				echo "	<td valign='top' class='".$row_style[$c]."' style='text-align: right;'>".(($row['tta'] > 0) ? $row['tta']."s" : "&nbsp;")."</td>\n";
			//duration
				echo "	<td valign='top' class='".$row_style[$c]."' style='text-align: center;'>".gmdate("G:i:s", $seconds)."</td>\n";
			//billing
				if (file_exists($_SERVER["PROJECT_ROOT"]."/app/billing/app_config.php")){

					$database->table = "v_xml_cdr";
					$accountcode = (strlen($row["accountcode"])?$row["accountcode"]:$_SESSION[domain_name]);
					$database->sql = "SELECT currency FROM v_billings WHERE type_value='$accountcode'";
					$database->result = $database->execute();
					$billing_currency = (strlen($database->result[0]['currency'])?$database->result[0]['currency']:'USD');
					$billing_currency = (strlen($database->result[0]['currency'])?$database->result[0]['currency']:
						(strlen($_SESSION['billing']['currency']['text'])?$_SESSION['billing']['currency']['text']:'USD')
					);
					unset($database->sql);
					unset($database->result);

					$sell_price = strlen($row['call_sell'])?$row['call_sell']:0;
					$lcr_direction = (strlen($row['direction'])?$row['direction']:"outbound");

					$xml_string = trim($row["xml"]);
					$json_string = trim($row["json"]);
					if (strlen($xml_string) > 0) {
						$format = "xml";
					}
					if (strlen($json_string) > 0) {
						$format = "json";
					}
					try {
						if ($format == 'json') {
							$array = json_decode($json_string,true);
						}
						if ($format == 'xml') {
							$array = json_decode(json_encode((array)simplexml_load_string($xml_string)),true);
						}
					}
					catch(Exception $e) {
						echo $e->getMessage();
					}

					$n = (($lcr_direction == "inbound")?
						check_str(urldecode($array["caller_profile"]["caller_id_number"])):
						check_str(urldecode($array["variables"]["lcr_query_digits"]))
					);

					$database->table = "v_lcr";
					$database->sql = "SELECT currency FROM v_lcr WHERE v_lcr.carrier_uuid IS NULL AND v_lcr.enabled='true' AND v_lcr.lcr_direction='$lcr_direction' AND v_lcr.digits IN (".number_series($n).") ORDER BY digits DESC, rate ASC, date_start DESC LIMIT 1";
					$database->result = $database->execute();
					// print "<pre>"; print $database->sql . ":";print "[".$database->result[0]['currency']."]"; print_r($array); print "</pre>";

					$lcr_currency = ((is_string($database->result[0]['currency']) && strlen($database->result[0]['currency']))?$database->result[0]['currency']:
						(strlen($_SESSION['billing']['currency']['text'])?$_SESSION['billing']['currency']['text']:'USD')
					);      //billed currency
					unset($database->sql);
					unset($database->result);
					if ($sell_price){
						$price = currency_convert($sell_price, $billing_currency, $lcr_currency);
					}
					else {
						$price = 0;
					}
					echo "	<td valign='top' class='".$row_style[$c]."'>".number_format($price,6)." $billing_currency</td>\n";
					unset ($sell_price, $price);
				}
			//pdd (post dial delay)
				if (permission_exists("xml_cdr_pdd")) {
					echo "	<td valign='top' class='".$row_style[$c]."' style='text-align: right;'>".number_format(escape($row['pdd_ms'])/1000,2)."s</td>\n";
				}
			//mos (mean opinion score)
				if (permission_exists("xml_cdr_mos")) {
					if(strlen($row['rtp_audio_in_mos']) > 0){
						$title = " title='".$text['label-mos_score-'.round($row['rtp_audio_in_mos'])]."'";
						$value = $row['rtp_audio_in_mos'];
					}
					echo "	<td valign='top' class='".$row_style[$c]."'$title style='text-align: center;'>$value</td>\n";
				}
			//hangup cause/call result
				if (permission_exists('hangup_cause')) {
					echo "	<td valign='top' class='".$row_style[$c]."' nowrap='nowrap'><a ".$tr_link.">".escape($hangup_cause)."</a></td>\n";
				}
				else {
					echo "	<td valign='top' class='".$row_style[$c]."' nowrap='nowrap'>".ucwords(escape($call_result))."</td>\n";
				}
			//control icons
				if (permission_exists('xml_cdr_details')) {
					echo "	<td class='list_control_icons tr_link_void' nowrap='nowrap'>";
					if ($tr_link!=null) {
						echo "		<a $tr_link title='".$text['button-view']."'>$v_link_label_view</a>"; //CJB
					}
					if (permission_exists('xml_cdr_delete')) {
						echo 	"<a href='xml_cdr_delete.php?id[]=".escape($row['xml_cdr_uuid'])."&rec[]=".(($record_path != '') ? base64_encode($record_path.'/'.$record_name) : null)."' alt='".$text['button-delete']."' onclick=\"return confirm('".$text['confirm-delete']."')\">".$v_link_label_delete."</a>";
					}
					echo "	</td>\n";
				}
			echo "</tr>\n";
			$c = ($c) ? 0 : 1;
		} //end foreach
		unset($sql, $result);
	} //end if results

	echo "<tr>\n";
	echo "</table>";
	echo "</form>";
	echo "<br><br>";

	if ($xml_cdr_total_hidden > 0) {
		echo $text['description-total_hidden'] . ": " . $xml_cdr_total_hidden . "/" . $result_count;
	}
	echo $paging_controls;
	echo "<br><br>";

	// check or uncheck all checkboxes
	if (sizeof($xml_ids) > 0) {
		echo "<script>\n";
		echo "	function check(what) {\n";
		foreach ($xml_ids as $xml_id) {
			echo "document.getElementById('".$xml_id."').checked = (what == 'all') ? true : false;\n";
		}
		echo "	}\n";
		echo "</script>\n";
	}

	//store last search/sort query parameters in session
	$_SESSION['xml_cdr']['last_query'] = $_SERVER["QUERY_STRING"];

//show the footer

	require_once "resources/footer.php";

?>
