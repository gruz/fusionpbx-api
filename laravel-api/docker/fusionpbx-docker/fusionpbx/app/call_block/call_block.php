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
	Portions created by the Initial Developer are Copyright (C) 2008-2012
	the Initial Developer. All Rights Reserved.

	Contributor(s):
	Mark J Crane <markjcrane@fusionpbx.com>

	Call Block is written by Gerrit Visser <gerrit308@gmail.com>
*/
require_once "root.php";
require_once "resources/require.php";

//check permissions
	require_once "resources/check_auth.php";
	if (permission_exists('call_block_view')) {
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

//get variables used to control the order
	$order_by = $_GET["order_by"];
	$order = $_GET["order"];

//show the content
	echo "<table width='100%' cellpadding='0' cellspacing='0' border='0'>\n";
	echo "	<tr>\n";
	echo "		<td width='50%' align='left' nowrap='nowrap'><b>".$text['title-call-block']."</b></td>\n";
	echo "		<td width='50%' align='right'>&nbsp;</td>\n";
	echo "	</tr>\n";
	echo "	<tr>\n";
	echo "		<td align='left' colspan='2'>\n";
	echo "			".$text['description-call-block']."<br /><br />\n";
	echo "		</td>\n";
	echo "	</tr>\n";
	echo "</table>\n";

//prepare to page the results
	$sql = "select count(*) as num_rows from v_call_block ";
	$sql .= "where domain_uuid = '".$_SESSION['domain_uuid']."' ";
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

//get the  list
	$sql = "select * from v_call_block ";
	$sql .= "where domain_uuid = '".$_SESSION['domain_uuid']."' ";
	if (strlen($order_by)> 0) { 
		$sql .= "order by $order_by $order ";
	} else {
		$sql .= "order by call_block_number asc "; 
	}
	$sql .= " limit $rows_per_page offset $offset ";
	$prep_statement = $db->prepare(check_sql($sql));
	$prep_statement->execute();
	$result = $prep_statement->fetchAll();
	$result_count = count($result);
	unset ($prep_statement, $sql);

//table headers
	$c = 0;
	$row_style["0"] = "row_style0";
	$row_style["1"] = "row_style1";
	echo "<table class='tr_hover' width='100%' border='0' cellpadding='0' cellspacing='0'>\n";
	echo "<tr>\n";
	echo th_order_by('call_block_number', $text['label-number'], $order_by, $order);
	echo th_order_by('call_block_name', $text['label-name'], $order_by, $order);
	echo th_order_by('call_block_count', $text['label-count'], $order_by, $order, '', "style='text-align: center;'");
	echo th_order_by('date_added', $text['label-date-added'], $order_by, $order);
	echo th_order_by('call_block_action', $text['label-action'], $order_by, $order);
	echo th_order_by('call_block_enabled', $text['label-enabled'], $order_by, $order);
	echo "<td class='list_control_icons'>";
	if (permission_exists('call_block_add')) {
		echo "<a href='call_block_edit.php' alt='".$text['button-add']."'>$v_link_label_add</a>";
	}
	echo "</td>\n";
	echo "</tr>\n";

//show the results
	if ($result_count > 0) {
		foreach($result as $row) {
			$tr_link = (permission_exists('call_block_edit')) ? "href='call_block_edit.php?id=".$row['call_block_uuid']."'" : null;
			echo "<tr ".$tr_link.">\n";
			echo "	<td valign='top' class='".$row_style[$c]."'>";
			if (permission_exists('call_block_edit')) {
				echo "<a href='call_block_edit.php?id=".escape($row['call_block_uuid'])."'>".escape($row['call_block_number'])."</a>";
			}
			else {
				echo escape($row['call_block_number']);
			}
			echo "	</td>\n";
			echo "	<td valign='top' class='".$row_style[$c]."'>".escape($row['call_block_name'])."</td>\n";
			echo "	<td valign='top' class='".$row_style[$c]."' style='text-align: center;'>".escape($row['call_block_count'])."</td>\n";
			if (defined('TIME_24HR') && TIME_24HR == 1) {
				$tmp_date_added = date("j M Y H:i:s", $row['date_added']);
			} else {
				$tmp_date_added = date("j M Y h:i:sa", $row['date_added']);
			}
			echo "	<td valign='top' class='".$row_style[$c]."'>".$tmp_date_added."</td>\n";
			echo "	<td valign='top' class='".$row_style[$c]."'>".escape($row['call_block_action'])."</td>\n";
			echo "	<td valign='top' class='".$row_style[$c]."'>".$text['label-'.escape($row['call_block_enabled'])]."</td>\n";
			echo "	<td class='list_control_icons'>";
			if (permission_exists('call_block_edit')) {
				echo "<a href='call_block_edit.php?id=".escape($row['call_block_uuid'])."' alt='".$text['button-edit']."'>$v_link_label_edit</a>";
			}
			if (permission_exists('call_block_delete')) {
				echo "<a href='call_block_delete.php?id=".escape($row['call_block_uuid'])."' alt='".$text['button-delete']."' onclick=\"return confirm('".$text['confirm-delete']."')\">$v_link_label_delete</a>";
			};
			echo "  </td>";
			echo "</tr>\n";
			$c = 1 - $c;  // Switch $c = 0/1/0...
		} //end foreach
		unset($sql, $result, $row_count);
	} //end if results

//complete the content
	echo "<tr>\n";
	echo "<td colspan='11' align='left'>\n";
	echo "	<table width='100%' cellpadding='0' cellspacing='0'>\n";
	echo "	<tr>\n";
	echo "		<td width='33.3%' nowrap>&nbsp;</td>\n";
	echo "		<td width='33.3%' align='center' nowrap>$paging_controls</td>\n";
	echo "		<td class='list_control_icons'>";
	if (permission_exists('call_block_add')) {
		echo "<a href='call_block_edit.php' alt='".$text['button-add']."'>$v_link_label_add</a>";
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
