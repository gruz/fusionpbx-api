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

if (permission_exists('destinations_ext_view')) {
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
	$document['title'] = $text['title-destination_ext'];

require_once "resources/paging.php";

//get variables used to control the order
	$order_by = escape($_GET["order_by"]);
	$order = escape($_GET["order"]);

	//prepare to page the results
	//get total destination count from the database
	$sql = "SELECT count(*) AS num_rows FROM v_destinations_ext ";
	$sql .= "WHERE (domain_uuid = '".$domain_uuid."' OR domain_uuid IS NULL) ";
	if (strlen($search) > 0) {
		$sql .= "AND (";
		$sql .= " 	destination_ext_number LIKE '%".$search."%' ";
		$sql .= " 	OR lower(destination_ext_variable) LIKE '%".strtolower($search)."%' ";
		$sql .= " 	OR lower(destination_ext_description) LIKE '%".strtolower($search)."%' ";
		$sql .= ")";
	}
	$prep_statement = $db->prepare($sql);
	if ($prep_statement) {
		$prep_statement->execute();
		$row = $prep_statement->fetch(PDO::FETCH_ASSOC);
		$num_rows = $row['num_rows'];
	}
	else {
		$num_rows = 0;
	}

	//prepare to page the results
	$rows_per_page = ($_SESSION['domain']['paging']['numeric'] != '') ? $_SESSION['domain']['paging']['numeric'] : 50;
	$param = "";
	$page = escape($_GET['page']);
	if (strlen($page) == 0) { $page = 0; $_GET['page'] = 0; }
	list($paging_controls, $rows_per_page, $var3) = paging($num_rows, $param, $rows_per_page);
	$offset = $rows_per_page * $page;

	//get the list
	$sql = "SELECT * FROM v_destinations_ext ";
	$sql .= "WHERE domain_uuid = '$domain_uuid' ";
	if (strlen($search) > 0) {
		$sql .= "AND (";
		$sql .= " 	destination_ext_number LIKE '%".$search."%' ";
		$sql .= " 	OR lower(destination_ext_variable) LIKE '%".strtolower($search)."%' ";
		$sql .= " 	OR lower(destination_ext_description) LIKE '%".strtolower($search)."%' ";
		$sql .= ") ";
	}
	if (strlen($order_by)> 0) { 
		$sql .= "ORDER BY $order_by $order, destination_ext_uuid ASC "; 
	} else {
		$sql .= "ORDER BY destination_ext_uuid ASC "; 
	}
	$sql .= "LIMIT $rows_per_page OFFSET $offset ";
	$prep_statement = $db->prepare(check_sql($sql));
	$prep_statement->execute();
	$result = $prep_statement->fetchAll();
	$result_count = count($result);
	unset ($prep_statement, $sql);


	echo "<table width='100%' cellpadding='0' cellspacing='0' border='0'>\n";
	echo "	<tr>\n";
	echo "		<td width='50%' align='left' nowrap='nowrap' valign='top'><b>".$text['header-destination_ext']." (".$num_rows.")</b></td>\n";
	echo "			<form method='get' action=''>\n";
	echo "			<td width='50%' align='right'>\n";
	echo "				<input type='text' class='txt' style='width: 150px' name='search' value='" . escape($search) . "'>";
	echo "				<input type='submit' class='btn' name='submit' value='".$text['button-search']."'>";
	echo "			</td>\n";
	echo "			</form>\n";
	echo "	</tr>\n";
	echo "	<tr>\n";
	echo "		<td align='left' colspan='2' valign='top'>\n";
	echo "			".$text['description-destination_ext']."<br /><br />\n";
	echo "		</td>\n";
	echo "	</tr>\n";
	echo "</table>\n";

	$c = 0;
	$row_style["0"] = "row_style0";
	$row_style["1"] = "row_style1";

	echo "<table class='tr_hover' width='100%' border='0' cellpadding='0' cellspacing='0'>\n";
	echo "<tr>\n";

	echo th_order_by('destination_ext_number', $text['label-destination_ext_number'], $order_by, $order, '', '', $param);
	echo th_order_by('destination_ext_variable', $text['label-destination_ext_variable'], $order_by, $order, '', '', $param);
	echo th_order_by('destination_ext_enabled', $text['label-destination_ext_status'], $order_by, $order, '', '', $param);
	echo th_order_by('destination_ext_description', $text['label-destination_ext_description'], $order_by, $order, '', '', $param);

	echo "<td class='list_control_icons'>";
	if (permission_exists('destinations_ext_add')) {
		echo "<a href='destination_ext_edit.php' alt='".$text['button-add']."'>$v_link_label_add</a>";
	}
	echo "</td>\n";
	echo "</tr>\n";

	if ($result_count > 0) {
		foreach($result as $row) {

			$row = array_map('escape', $row);

			$tr_link = (permission_exists('destinations_ext_edit')) ? "href='destination_ext_edit.php?id=".$row['destination_ext_uuid']."'" : null;
			echo "<tr ".$tr_link.">\n";
			echo "	<td valign='top' class='".$row_style[$c]."'>".$row['destination_ext_number']."&nbsp;</td>\n";
			echo "	<td valign='top' class='".$row_style[$c]."'>".$row['destination_ext_variable']."&nbsp;</td>\n";
			if ($row['destination_ext_enabled'] == 'true') {
  				echo "	<td valign='top' class='".$row_style[$c]."'>".$text['label-true']."&nbsp;</td>\n";
			} else {
  				echo "	<td valign='top' class='".$row_style[$c]."'>".$text['label-false']."&nbsp;</td>\n";
			}
			echo "	<td valign='top' class='".$row_style[$c]."'>".$row['destination_ext_description']."&nbsp;</td>\n";
			echo "	<td class='list_control_icons'>";
			if (permission_exists('destinations_ext_edit')) {
				echo "<a href='destination_ext_edit.php?id=".$row['destination_ext_uuid']."' alt='".$text['button-edit']."'>$v_link_label_edit</a>";
			}
			if (permission_exists('destinations_ext_delete')) {
				echo "<a href='destination_ext_delete.php?id=".$row['destination_ext_uuid']."' alt='".$text['button-delete']."' onclick=\"return confirm('".$text['confirm-delete']."')\">$v_link_label_delete</a>";
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
	if (permission_exists('destinations_ext_add')) {
		echo 		"<a href='destination_ext_edit.php' alt='".$text['button-add']."'>$v_link_label_add</a>";
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
