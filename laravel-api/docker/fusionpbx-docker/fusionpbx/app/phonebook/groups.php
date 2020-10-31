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
	Igor Olhovskiy <igorolhovskiy@gmail.com>
*/
require_once "root.php";
require_once "resources/require.php";

//check permissions
	require_once "resources/check_auth.php";
	if (permission_exists('phonebook_view')) {
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

	echo "<table width='100%' cellpadding='0' cellspacing='0 border='0'>\n";
	echo "	<tr>\n";
	echo "		<td width='50%' align='left' nowrap='nowrap'><b>".$text['title-groups']."</b></td>\n";
	echo "		<td width='50%' align='right'>&nbsp;</td>\n";
	echo "		<td width='70%' align='right'>";
	echo "			<input type='button' class='btn' name='' alt='".$text['button-back']."' onclick=\"window.location=' phonebook.php'\" value='".$text['button-back']."'>";
	echo "		</td>\n";
	echo "	</tr>\n";
	echo "	<tr>\n";
	echo "		<td align='left' colspan='2'>\n";
	echo "			".$text['description-groups']."<br /><br />\n";
	echo "		</td>\n";
	echo "	</tr>\n";
	echo "</table>\n";

//get variables used to control the order
	$order_by = $_GET["order_by"];
	$order = $_GET["order"];

//set domain variable
	$domain_name = $_SESSION['domain_name'];

//prepare to page the results
	$sql = "SELECT count(*) AS num_rows FROM v_phonebook_groups ";
	$sql .= "WHERE domain_uuid = '".$domain_uuid."' ";
	if (strlen($order_by)> 0) { 
		$sql .= "order by $order_by $order ";
	}
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
	// Get all phonebook enties for this domain.
	$sql = "SELECT * FROM v_phonebook_groups WHERE domain_uuid = '".$_SESSION['domain_uuid']."' ";
	if (strlen($order_by)> 0) { 
		$sql .= "ORDER BY $order_by $order"; 
	}else{
		$sql .= "ORDER BY group_name ASC";
	}
	$sql .= " LIMIT $rows_per_page OFFSET $offset ";
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
	echo th_order_by('group_name', $text['label-name'], $order_by, $order);
    echo th_order_by('group_desc', $text['label-description'], $order_by, $order);
	echo "<td class='list_control_icons'>";
	if (permission_exists('phonebook_group_add')) {
		echo "<a href='groups_edit.php' alt='" . $text['button-add'] . "'>$v_link_label_add</a>";
	}
	echo "</td>\n";
	echo "</tr>\n";
//show the results
	if ($result_count > 0) {
		foreach($result as $row) {

			$row = array_map("escape", $row);

			$tr_link = (permission_exists('phonebook_group_edit')) ? "href='groups_edit.php?id=".$row['group_uuid']."'" : null;
			echo "<tr ".$tr_link.">\n";
			echo "  <td valign='top' class='".$row_style[$c]."' style='text-align: left;'>" . $row['group_name'] . "</td>\n";
			echo "  <td valign='top' class='".$row_style[$c]."' style='text-align: left;'>" . $row['group_desc'] . "</td>\n";
			echo "	<td class='list_control_icons'>";
			if (permission_exists('phonebook_group_edit')) {
				echo "<a href='groups_edit.php?id=".$row['group_uuid']."' alt='".$text['button-edit']."'>$v_link_label_edit</a>";
			}
			if (permission_exists('phonebook_group_delete')) {
				echo "<a href='groups_delete.php?id=".$row['group_uuid']."' alt='".$text['button-delete']."' onclick=\"return confirm('".$text['confirm-delete']."')\">$v_link_label_delete</a>";
			};
			echo "  </td>";
			echo "</tr>\n";
			$c = 1 - $c;
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
	if (permission_exists('phonebook_group_add')) {
		echo "<a href='groups_edit.php' alt='".$text['button-add']."'>$v_link_label_add</a>";
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
