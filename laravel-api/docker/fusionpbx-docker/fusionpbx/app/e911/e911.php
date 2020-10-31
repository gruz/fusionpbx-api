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
*/
require_once "root.php";
require_once "resources/require.php";
require_once "resources/check_auth.php";

if (permission_exists('e911_view')) {
	//access granted
}
else {
	echo "access denied";
	exit;
}

//add multi-lingual support
	$language = new text;
	$text = $language->get();

require_once "resources/header.php";
	$document['title'] = $text['title-e911'];

require_once "resources/paging.php";

//get variables used to control the order
	$order_by = $_GET["order_by"];
	$order = $_GET["order"];

//show the content
	echo "<table width='100%' cellpadding='0' cellspacing='0 border='0'>\n";
	echo "	<tr>\n";
	echo "		<td width='50%' align='left' nowrap='nowrap'><b>".$text['header-e911']."</b></td>\n";
	echo "		<td width='50%' align='right'>&nbsp;</td>\n";
	echo "	</tr>\n";
	echo "	<tr>\n";
	echo "		<td align='left' colspan='2'>\n";
	echo "			".$text['description-e911']."<br /><br />\n";
	echo "		</td>\n";
	echo "	</tr>\n";
	echo "</table>\n";

	//prepare to page the results
		$sql = "select count(*) as num_rows from v_e911 ";
		$sql .= "where domain_uuid = '$domain_uuid' ";
		if (strlen($order_by)> 0) { $sql .= "order by $order_by $order "; }
		$prep_statement = $db->prepare($sql);
		if ($prep_statement) {
		$prep_statement->execute();
			$row = $prep_statement->fetch(PDO::FETCH_ASSOC);
			if ($row['num_rows'] > 0) {
				$num_rows = $row['num_rows'];
			}
			else {
				$num_rows = '0';
			}
		}

	//prepare to page the results
		$rows_per_page = ($_SESSION['domain']['paging']['numeric'] != '') ? $_SESSION['domain']['paging']['numeric'] : 50;
		$param = "";
		$page = $_GET['page'];
		if (strlen($page) == 0) { $page = 0; $_GET['page'] = 0; }
		list($paging_controls, $rows_per_page, $var3) = paging($num_rows, $param, $rows_per_page);
		$offset = $rows_per_page * $page;

	//get the list
		$sql = "select * from v_e911 ";
		$sql .= "where domain_uuid = '$domain_uuid' ";
		if (strlen($order_by)> 0) { $sql .= "order by $order_by $order "; }
		$sql .= "limit $rows_per_page offset $offset ";
		$prep_statement = $db->prepare(check_sql($sql));
		$prep_statement->execute();
		$result = $prep_statement->fetchAll();
		$result_count = count($result);
		unset ($prep_statement, $sql);

	$c = 0;
	$row_style["0"] = "row_style0";
	$row_style["1"] = "row_style1";

	echo "<table class='tr_hover' width='100%' border='0' cellpadding='0' cellspacing='0'>\n";
	echo "<tr>\n";
	echo th_order_by('e911_did', $text['label-e911_did'], $order_by, $order);
	echo th_order_by('e911_callername', $text['label-e911_callername'], $order_by, $order);
	echo th_order_by('e911_city', $text['label-e911_city'], $order_by, $order);
	echo th_order_by('e911_validated', $text['label-e911_validated'], $order_by, $order);
	echo "<td class='list_control_icons'>";
	if (permission_exists('e911_add')) {
		echo "<a href='e911_edit.php' alt='".$text['button-add']."'>$v_link_label_add</a>";
	}
	echo "</td>\n";
	echo "</tr>\n";

	if ($result_count > 0) {
		foreach($result as $row) {
			$row = array_map('escape', $row);

			$tr_link = (permission_exists('e911_edit')) ? "href='e911_edit.php?id=".$row['e911_uuid']."'" : null;
			echo "<tr ".$tr_link.">\n";
			echo "	<td valign='top' class='".$row_style[$c]."'>".escape(format_phone($row['e911_did']))."&nbsp;</td>\n";
			echo "	<td valign='top' class='".$row_style[$c]."'>".escape($row['e911_callername'])."&nbsp;</td>\n";
			echo "	<td valign='top' class='".$row_style[$c]."'>".escape($row['e911_city'])."&nbsp;</td>\n";
			if ($row['e911_validated'] == '') {
  				echo "	<td valign='top' class='".$row_style[$c]."'>".$text['label-false']."&nbsp;</td>\n";
			} else {
  				echo "	<td valign='top' class='".$row_style[$c]."'>".$row['e911_validated']."&nbsp;</td>\n";
			}
			//echo "	<td valign='top' class='row_stylebg' width='30%'>".$row['call_flow_description']."&nbsp;</td>\n";
			echo "	<td class='list_control_icons'>";
			if (permission_exists('e911_edit')) {
				echo "<a href='e911_edit.php?id=".$row['e911_uuid']."' alt='".$text['button-edit']."'>$v_link_label_edit</a>";
			}
			if (permission_exists('e911_delete')) {
				echo "<a href='e911_delete.php?id=".$row['e911_uuid']."' alt='".$text['button-delete']."' onclick=\"return confirm('".$text['confirm-delete']."')\">$v_link_label_delete</a>";
			}
			echo "	</td>\n";
			echo "</tr>\n";
			$c = 1 - $c;
		} //end foreach
		unset($sql, $result, $row_count);
	} //end if results

	echo "<tr>\n";
	echo "<td colspan='10' align='left'>\n";
	echo "	<table width='100%' cellpadding='0' cellspacing='0'>\n";
	echo "	<tr>\n";
	echo "		<td width='33.3%' nowrap>&nbsp;</td>\n";
	echo "		<td width='33.3%' align='center' nowrap>$paging_controls</td>\n";
	echo "		<td class='list_control_icons'>";
	if (permission_exists('e911_add')) {
		echo 		"<a href='e911_edit.php' alt='".$text['button-add']."'>$v_link_label_add</a>";
	}
	echo "		</td>\n";
	echo "	</tr>\n";
 	echo "	</table>\n";
	echo "</td>\n";
	echo "</tr>\n";

	echo "</table>";
	echo "<br /><br />";

//include the footer
	require_once "resources/footer.php";
?>
