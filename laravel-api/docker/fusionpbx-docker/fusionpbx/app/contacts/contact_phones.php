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
	require_once "resources/check_auth.php";

//check permissions
	if (permission_exists('contact_phone_view')) {
		//access granted
	}
	else {
		echo "access denied";
		exit;
	}

//get the contact list
	$sql = "select * from v_contact_phones ";
	$sql .= "where domain_uuid = '$domain_uuid' ";
	$sql .= "and contact_uuid = '$contact_uuid' ";
	$sql .= "order by phone_primary desc, phone_label asc ";
	$prep_statement = $db->prepare(check_sql($sql));
	$prep_statement->execute();
	$contact_phones = $prep_statement->fetchAll(PDO::FETCH_NAMED);
	unset ($prep_statement, $sql);

//set the row style
	$c = 0;
	$row_style["0"] = "row_style0";
	$row_style["1"] = "row_style1";

//javascript function: send_cmd
	echo "<script type=\"text/javascript\">\n";
	echo "function send_cmd(url) {\n";
	echo "	if (window.XMLHttpRequest) {// code for IE7+, Firefox, Chrome, Opera, Safari\n";
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

//show the content
	echo "<table width='100%' border='0'>\n";
	echo "<tr>\n";
	echo "<td width='50%' align='left' nowrap='nowrap'><b>".$text['label-phone_numbers']."</b></td>\n";
	echo "<td width='50%' align='right'>&nbsp;</td>\n";
	echo "</tr>\n";
	echo "</table>\n";

	echo "<table class='tr_hover' style='margin-bottom: 20px;' width='100%' border='0' cellpadding='0' cellspacing='0'>\n";
	echo "<tr>\n";
	echo "<th>".$text['label-phone_label']."</th>\n";
	echo "<th>".$text['label-phone_number']."</th>\n";
	echo "<th>".$text['label-phone_type']."</th>\n";
	echo "<th>".$text['label-phone_tools']."</th>\n";
	echo "<th>".$text['label-phone_description']."</th>\n";
	echo "<td class='list_control_icons'>";
	if (permission_exists('contact_phone_add')) {
		echo "<a href='contact_phone_edit.php?contact_uuid=".escape($_GET['id'])."' alt='".$text['button-add']."'>$v_link_label_add</a>";
	}
	echo "</td>\n";
	echo "</tr>\n";
	if (is_array($contact_phones)) {
		foreach($contact_phones as $row) {
			if (permission_exists('contact_phone_edit')) {
				$tr_link = "href='contact_phone_edit.php?contact_uuid=".escape($row['contact_uuid'])."&id=".escape($row['contact_phone_uuid'])."'";
			}
			echo "<tr ".$tr_link." ".((escape($row['phone_primary'])) ? "style='font-weight: bold;'" : null).">\n";
			echo "	<td valign='top' class='".$row_style[$c]."'>".(($row['phone_label'] == strtolower($row['phone_label'])) ? ucwords($row['phone_label']) : $row['phone_label'])."&nbsp;</td>\n";
			echo "	<td valign='top' class='".$row_style[$c]." tr_link_void'>\n";
			echo "		<a href=\"javascript:void(0)\" onclick=\"send_cmd('".PROJECT_PATH."/app/click_to_call/click_to_call.php?src_cid_name=".escape(urlencode($row['phone_number']))."&src_cid_number=".escape(urlencode($row['phone_number']))."&dest_cid_name=".urlencode($_SESSION['user']['extension'][0]['outbound_caller_id_name'])."&dest_cid_number=".urlencode(escape($_SESSION['user']['extension'][0]['outbound_caller_id_number']))."&src=".urlencode(escape($_SESSION['user']['extension'][0]['user']))."&dest=".escape(urlencode($row['phone_number']))."&rec=false&ringback=us-ring&auto_answer=true');\">\n";
			echo "		".escape(format_phone($row['phone_number']))."</a>&nbsp;\n";
			echo "	</td>\n";
			echo "	<td valign='top' class='".$row_style[$c]."'>\n";
			if ($row['phone_type_voice']) { $phone_types[] = $text['label-voice']; }
			if ($row['phone_type_fax']) { $phone_types[] = $text['label-fax']; }
			if ($row['phone_type_video']) { $phone_types[] = $text['label-video']; }
			if ($row['phone_type_text']) { $phone_types[] = $text['label-text']; }
			if (is_array($phone_types)) {
				echo "	".implode(", ", $phone_types)."\n";
			}
			unset($phone_types);
			echo "	</td>\n";
			echo "	<td valign='top' class='".$row_style[$c]." tr_link_void' nowrap='nowrap'>\n";
			echo "		<a href=\"".PROJECT_PATH."/app/xml_cdr/xml_cdr.php?caller_id_number=".escape($row['phone_number'])."\">CDR</a>\n";
			if ($row['phone_type_voice']) {
				echo "		&nbsp;\n";
				echo "		<a href=\"javascript:void(0)\" onclick=\"send_cmd('".PROJECT_PATH."/app/click_to_call/click_to_call.php?src_cid_name=".escape(urlencode($row['phone_number']))."&src_cid_number=".escape(urlencode($row['phone_number']))."&dest_cid_name=".urlencode(escape($_SESSION['user']['extension'][0]['outbound_caller_id_name']))."&dest_cid_number=".urlencode(escape($_SESSION['user']['extension'][0]['outbound_caller_id_number']))."&src=".urlencode(escape($_SESSION['user']['extension'][0]['user']))."&dest=".escape(urlencode($row['phone_number']))."&rec=false&ringback=us-ring&auto_answer=true');\">".$text['label-phone_call']."</a>\n";
			}
			echo "	</td>\n";
			echo "	<td valign='top' class='row_stylebg'>".escape($row['phone_description'])."&nbsp;</td>\n";
			echo "	<td class='list_control_icons'>";
			if (permission_exists('contact_phone_edit')) {
				echo "<a href='contact_phone_edit.php?contact_uuid=".escape($row['contact_uuid'])."&id=".escape($row['contact_phone_uuid'])."' alt='".$text['button-edit']."'>$v_link_label_edit</a>";
			}
			if (permission_exists('contact_phone_delete')) {
				echo "<a href='contact_phone_delete.php?contact_uuid=".escape($row['contact_uuid'])."&id=".escape($row['contact_phone_uuid'])."' alt='".$text['button-delete']."' onclick=\"return confirm('".$text['confirm-delete']."')\">$v_link_label_delete</a>";
			}
			echo "	</td>\n";
			echo "</tr>\n";
			$c = ($c) ? 0 : 1;
		} //end foreach
		unset($sql, $contact_phones);
	} //end if results

	echo "</table>";

?>
