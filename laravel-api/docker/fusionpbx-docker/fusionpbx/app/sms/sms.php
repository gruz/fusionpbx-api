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
	Portions created by the Initial Developer are Copyright (C) 2008-2016
	the Initial Developer. All Rights Reserved.

	Contributor(s):
	Mark J Crane <markjcrane@fusionpbx.com>
	Igor Olhovskiy <igorolhovskiy@gmail.com>
*/
include "root.php";
require_once "resources/require.php";
require_once "resources/check_auth.php";
if (permission_exists('sms_message_view')) {
	//access granted
}
else {
	echo "access denied";
	exit;
}

//add multi-lingual support
$language = new text;
$text = $language->get();

//get the http values and set them as variables
$order_by = check_str($_GET["order_by"]);
$order = check_str($_GET["order"]);

//handle search term
$search = check_str($_GET["search"]);
if (strlen($search) > 0) {
	$search = strtolower($search);
	$sql_search = "AND ( ";
	$sql_search .= "lower(sms_message_text) LIKE '%".$search."%' ";
	$sql_search .= "OR lower(sms_message_to) LIKE '%".$search."%' ";
	$sql_search .= "OR lower(sms_message_from) LIKE '%".$search."%' ";
	$sql_search .= "OR lower(sms_message_direction) LIKE '%".$search."%' ";
	$sql_search .= ") ";
}

//additional includes
require_once "resources/header.php";
require_once "resources/paging.php";


//get total sms count from the database

$sql = "SELECT count(*) AS num_rows FROM v_sms_messages";
$sql .= " WHERE domain_uuid = '" .$domain_uuid . "' ";
$sql .= $sql_search;

$prep_statement = $db->prepare($sql);
if ($prep_statement) {
	$prep_statement->execute();
	$row = $prep_statement->fetch(PDO::FETCH_ASSOC);
	$total_sms_messages = $row['num_rows'];
}
unset($prep_statement, $row);

//prepare to page the results
$rows_per_page = ($_SESSION['domain']['paging']['numeric'] != '') ? $_SESSION['domain']['paging']['numeric'] : 50;
if (!isset($_GET['page'])) { $_GET['page'] = 0; }
$_GET['page'] = check_str($_GET['page']);
list($paging_controls_mini, $rows_per_page, $var_3) = paging($total_sms, $param, $rows_per_page, true); //top
list($paging_controls, $rows_per_page, $var_3) = paging($total_sms, $param, $rows_per_page); //bottom
$offset = $rows_per_page * $_GET['page'];

//get the extensions
$sql = "SELECT * FROM v_sms_messages ";
$sql .= "WHERE domain_uuid = '$domain_uuid' ";
$sql .= $sql_search;

if (strlen($order_by)> 0) { 
	$sql .= "ORDER BY $order_by $order "; 
}
else {
	$sql .= "ORDER BY sms_message_timestamp DESC";
}
$sql .= " LIMIT $rows_per_page OFFSET $offset ";
$prep_statement = $db->prepare(check_sql($sql));
$prep_statement->execute();
$sms_messages = $prep_statement->fetchAll(PDO::FETCH_NAMED);

unset ($prep_statement, $sql);

//set the alternating styles
$c = 0;
$row_style["0"] = "row_style0";
$row_style["1"] = "row_style1";

//show the content
echo "<table width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\">\n";
echo "  <tr>\n";
echo "	<td align='left' width='100%'>\n";
echo "		<b>".$text['header-sms_message']." (".$total_sms_messages.")</b><br>\n";
echo "	</td>\n";
echo "		<form method='get' action=''>\n";
echo "			<td style='vertical-align: top; text-align: right; white-space: nowrap;'>\n";
if (permission_exists('sms_routing_view')) { // Show button only if SMS routing view enabled
	echo "				<input type='button' class='btn' value='" . $text['button-sms_routing'] . "' onclick=\"window.location='sms_routing.php';\">\n";
}
echo "				<input type='text' class='txt' style='width: 150px; margin-left: 15px;' name='search' id='search' value='" . escape($search) . "'>";
echo "				<input type='submit' class='btn' name='submit' value='" . $text['button-search'] . "'>";
if ($paging_controls_mini != '') {
	echo 			"<span style='margin-left: 15px;'>" . $paging_controls_mini . "</span>\n";
}
echo "			</td>\n";
echo "		</form>\n";
echo "  </tr>\n";
echo "	<tr>\n";
echo "		<td colspan='2'>\n";
echo "			" . $text['description-sms_messages'] . "\n";
echo "		</td>\n";
echo "	</tr>\n";
echo "</table>\n";
echo "<br />";

echo "<form name='frm' method='post' action='sms_delete.php'>\n";
echo "<table class='tr_hover' width='100%' border='0' cellpadding='0' cellspacing='0'>\n";
echo "<tr>\n";

if (permission_exists('sms_message_delete')) {
	echo "<th style='width: 30px; text-align: center; padding: 0px;'><input type='checkbox' id='chk_all' onchange=\"(this.checked) ? check('all') : check('none');\"></th>";
}

echo th_order_by('sms_message_timestamp', $text['label-sms_message_timestamp'], $order_by, $order);
echo th_order_by('sms_message_from', $text['label-sms_message_from'], $order_by, $order);
echo th_order_by('sms_message_to', $text['label-sms_message_to'], $order_by, $order);
echo " <th>" . $text['label-sms_message_text'] . "</th>\n";
echo th_order_by('sms_message_direction', $text['label-sms_message_direction'], $order_by, $order);
echo th_order_by('sms_message_status', $text['label-sms_message_status'], $order_by, $order);

echo "<td class='list_control_icon'>\n";
if (permission_exists('sms_message_delete') && is_array($sms_messages)) {
	echo "<a href='javascript:void(0);' onclick=\"if (confirm('".$text['confirm-delete']."')) { document.forms.frm.submit(); }\" alt='".$text['button-delete']."'>".$v_link_label_delete."</a>";
}
echo "</td>\n";

echo "</tr>\n";

if (is_array($sms_messages)) {

	$sms_message_ids = array();

	foreach($sms_messages as $row) {
		echo "<tr>\n";
		if (permission_exists('sms_message_delete')) {
			echo "	<td valign='top' class='".$row_style[$c]." tr_link_void' style='text-align: center; vertical-align: middle; padding: 0px;'>";
			echo "		<input type='checkbox' name='id[]' id='checkbox_".escape($row['sms_message_uuid'])."' value='".escape($row['sms_message_uuid'])."' onclick=\"if (!this.checked) { document.getElementById('chk_all').checked = false; }\">";
			echo "	</td>\n";
			$sms_message_ids[] = 'checkbox_'.$row['sms_message_uuid'];
		}

		$sms_message_timestamp = ($_SESSION['domain']['time_format']['text'] == '12h') ? date("j M Y g:i:sa", strtotime($row['sms_message_timestamp'])) : date("j M Y H:i:s", strtotime($row['sms_message_timestamp']));
		
		echo "	<td valign='top' class='".$row_style[$c]."' width='10%'>".$sms_message_timestamp."&nbsp;</td>\n";
		echo "	<td valign='top' class='".$row_style[$c]."' width='13%'>".escape($row['sms_message_from'])."&nbsp;</td>\n";
		echo "	<td valign='top' class='".$row_style[$c]."' width='12%'>".escape($row['sms_message_to'])."</td>\n";
		echo "	<td valign='top' class='".$row_style[$c]."' width='45%'>".escape($row['sms_message_text'])."</td>\n";
		echo "	<td valign='top' class='".$row_style[$c]."' width='5%'>".escape($row['sms_message_direction'])."</td>\n";
		echo "	<td valign='top' class='".$row_style[$c]."' width='15%'>".escape($row['sms_message_status'])."</td>\n";

		echo "	<td class='list_control_icons'>";
		if (permission_exists('sms_message_delete')) {
			echo "<a href='sms_delete.php?id[]=".escape($row['sms_message_uuid'])."' alt='".$text['button-delete']."' onclick=\"return confirm('".$text['confirm-delete']."')\">$v_link_label_delete</a>";
		}
		echo "</td>\n";
		echo "</tr>\n";
		$c = ($c) ? 0 : 1;
	}
}

echo "</table>";
echo "</form>";

if (strlen($paging_controls) > 0) {
	echo "<br />";
	echo $paging_controls."\n";
}

echo "<br /><br />";

// check or uncheck all checkboxes
if (sizeof($sms_message_ids) > 0) {
	echo "<script>\n";
	echo "	function check(what) {\n";
	echo "		document.getElementById('chk_all').checked = (what == 'all') ? true : false;\n";
	foreach ($sms_message_ids as $sms_message_id) {
		echo "		document.getElementById('".$sms_message_id."').checked = (what == 'all') ? true : false;\n";
	}
	echo "	}\n";
	echo "</script>\n";
}

if (is_array($sms_messages)) {
	// check all checkboxes
	key_press('ctrl+a', 'down', 'document', null, null, "check('all');", true);

	// delete checked
	key_press('delete', 'up', 'document', array('#search'), $text['confirm-delete'], 'document.forms.frm.submit();', true);
}

//show the footer
require_once "resources/footer.php";

?>