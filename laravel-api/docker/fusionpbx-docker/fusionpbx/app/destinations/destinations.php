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
	if (permission_exists('destination_view')) {
		//access granted
	}
	else {
		echo "access denied";
		exit;
	}

//add multi-lingual support
	$language = new text;
	$text = $language->get();

//get the action
	if (is_array($_POST["destinations"])) {
		$destinations = $_POST["destinations"];
		foreach($destinations as $row) {
			if ($row['action'] == 'delete') {
				$action = 'delete';
				break;
			}
		}
	}

//delete the destinations
	if (permission_exists('destination_delete')) {
		if ($action == "delete") {
			//download
				$obj = new destinations;
				$obj->delete($destinations);
			//delete message
				messages::add($text['message-delete']);
		}
	}

//get variables used to control the order
	$order_by = check_str($_GET["order_by"]);
	$order = check_str($_GET["order"]);

//set the type
	if ($_GET['type'] == 'inbound') {
		$destination_type = 'inbound';
	}
	elseif ($_GET['type'] == 'outbound') {
		$destination_type = 'outbound';
	}
	elseif ($_GET['type'] == 'local') {
		$destination_type = 'local';
	}
	else {
		$destination_type = 'inbound';
	}

//add the search term
	$search = strtolower(check_str($_GET["search"]));
	if (strlen($search) > 0) {
		$sql_search = " (";
		$sql_search .= "lower(destination_type) like '%".$search."%' ";
		$sql_search .= "or lower(destination_number) like '%".$search."%' ";
		$sql_search .= "or lower(destination_context) like '%".$search."%' ";
		$sql_search .= "or lower(destination_accountcode) like '%".$search."%' ";
		if (permission_exists('outbound_caller_id_select')) {
			$sql_search .= "or lower(destination_caller_id_name) like '%".$search."%' ";
			$sql_search .= "or destination_caller_id_number like '%".$search."%' ";
		}
		$sql_search .= "or lower(destination_enabled) like '%".$search."%' ";
		$sql_search .= "or lower(destination_description) like '%".$search."%' ";
		$sql_search .= ") ";
	}

//additional includes
	require_once "resources/header.php";
	require_once "resources/paging.php";

//prepare to page the results
	$sql = "select count(destination_uuid) as num_rows from v_destinations ";
	$sql .= "where destination_type = '".$destination_type."' ";
	if ($_GET['show'] == "all" && permission_exists('destination_all')) {
		//show all
	} else {
		$sql .= "and (domain_uuid = '".$domain_uuid."' or domain_uuid is null) ";
	}
	if (isset($sql_search)) {
			$sql .= "and ".$sql_search;
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
	$param = "&search=".escape($search);
	if ($_GET['show'] == "all" && permission_exists('destination_all')) {
		$param .= "&show=all";
	}

	$page = $_GET['page'];
	if (strlen($page) == 0) { $page = 0; $_GET['page'] = 0; }
	list($paging_controls, $rows_per_page, $var3) = paging($num_rows, $param, $rows_per_page);
	$offset = $rows_per_page * $page;

//get the list
	$sql = "select * from v_destinations ";
	$sql .= "where destination_type = '".$destination_type."' ";
	if ($_GET['show'] == "all" && permission_exists('destination_all')) {
		//show all
	} else {
		$sql .= "and (domain_uuid = '".$domain_uuid."' or domain_uuid is null) ";
	}
	if (isset($sql_search)) {
		$sql .= "and ".$sql_search;
	}
	$sql .= "and destination_type = '".$destination_type."' ";
	if (strlen($order_by)> 0) { 
		$sql .= "order by $order_by $order, destination_uuid asc "; 
	} else {
		$sql .= "order by destination_uuid asc "; 
	}
	$sql .= "limit $rows_per_page offset $offset ";
	$prep_statement = $db->prepare(check_sql($sql));
	$prep_statement->execute();
	$destinations = $prep_statement->fetchAll(PDO::FETCH_NAMED);
	unset ($prep_statement, $sql);

//alternate the row style
	$c = 0;
	$row_style["0"] = "row_style0";
	$row_style["1"] = "row_style1";

//define the checkbox_toggle function
	echo "<script type=\"text/javascript\">\n";
	echo "	function checkbox_toggle(item) {\n";
	echo "		var inputs = document.getElementsByTagName(\"input\");\n";
	echo "		for (var i = 0, max = inputs.length; i < max; i++) {\n";
	echo "		    if (inputs[i].type === 'checkbox') {\n";
	echo "		       	if (document.getElementById('checkbox_all').checked == true) {\n";
	echo "				inputs[i].checked = true;\n";
	echo "			}\n";
	echo "				else {\n";
	echo "					inputs[i].checked = false;\n";
	echo "				}\n";
	echo "			}\n";
	echo "		}\n";
	echo "	}\n";
	echo "</script>\n";

//show the content
	echo "<table width='100%' border='0'>\n";
	echo "	<tr>\n";
	echo "		<td width='50%' align='left' nowrap='nowrap'><b>".$text['title-destinations']."  (".$num_rows.")</b></td>\n";
	echo "		<form method='get' action=''>\n";
	echo "			<td width='50%' style='vertical-align: top; text-align: right; white-space: nowrap;'>\n";

	echo "				<input type='button' class='btn' value='".$text['button-inbound']."' onclick=\"window.location='destinations.php?type=inbound';\">\n";
	echo "				<input type='button' class='btn' value='".$text['button-outbound']."' onclick=\"window.location='destinations.php?type=outbound';\">\n";
	//echo "				<input type='button' class='btn' value='".$text['button-local']."' onclick=\"window.location='destinations.php?type=local';\">\n";
	echo "				&nbsp;\n";
	if (permission_exists('destination_import')) {
		echo "				<input type='button' class='btn' alt='".$text['button-import']."' onclick=\"window.location='/app/destination_imports/destination_imports.php'\" value='".$text['button-import']."'>\n";
	}

	if (permission_exists('destination_all')) {
		if ($_GET['show'] == 'all') {
			echo "		<input type='hidden' name='show' value='all'>";
		}
		else {
			echo "		<input type='button' class='btn' value='".$text['button-show_all']."' onclick=\"window.location='destinations.php?show=all&type=".urlencode($destination_type)."';\">\n";
		}
	}

	echo "				<input type='text' class='txt' style='width: 150px; margin-left: 15px;' name='search' id='search' value='".escape($search)."'>\n";
	echo "				<input type='submit' class='btn' name='submit' value='".$text['button-search']."'>\n";
	echo "			</td>\n";
	echo "		</form>\n";
	echo "	</tr>\n";
	echo "	<tr>\n";
	echo "		<td align='left' colspan='2' valign='top'>\n";
	echo "			".$text['description-destinations']."<br /><br />\n";
	echo "		</td>\n";
	echo "	</tr>\n";
	echo "</table>\n";

	echo "<form method='post' action=''>\n";
	echo "<table class='tr_hover' width='100%' border='0' cellpadding='0' cellspacing='0'>\n";
	echo "<tr>\n";
	echo "	<th style='width:30px;'>\n";
	echo "		<input type='checkbox' name='checkbox_all' id='checkbox_all' value='' onclick=\"checkbox_toggle();\">\n";
	echo "	</th>\n";
	if ($_GET['show'] == "all" && permission_exists('destination_all')) {
		echo th_order_by('domain_name', $text['label-domain'], $order_by, $order, $param);
	}
	echo th_order_by('destination_type', $text['label-destination_type'], $order_by, $order, $param);
	echo th_order_by('destination_number', $text['label-destination_number'], $order_by, $order, $param);
	echo th_order_by('destination_context', $text['label-destination_context'], $order_by, $order, $param);
	if (permission_exists('outbound_caller_id_select')) {
		echo th_order_by('destination_caller_id_name', $text['label-destination_caller_id_name'], $order_by, $order, $param);
		echo th_order_by('destination_caller_id_number', $text['label-destination_caller_id_number'], $order_by, $order, $param);
	}
	echo th_order_by('destination_enabled', $text['label-destination_enabled'], $order_by, $order, $param);
	echo th_order_by('destination_description', $text['label-destination_description'], $order_by, $order, $param);
	echo "	<td class='list_control_icons'>";
	if (permission_exists('destination_add')) {
		echo "		<a href='destination_edit.php?type=$destination_type' alt='".$text['button-add']."'>$v_link_label_add</a>";
	}
	else {
		echo "&nbsp;\n";
	}
	echo "	</td>\n";
	echo "<tr>\n";

	if (is_array($destinations)) {
		$x = 0;
		foreach($destinations as $row) {
			if (permission_exists('destination_edit')) {
				$tr_link = "href='destination_edit.php?id=".urlencode($row['destination_uuid'])."'";
			}
			echo "<tr ".$tr_link.">\n";
			//echo "	<td valign='top' class=''>".$row['destination_uuid']."&nbsp;</td>\n";
			//echo "	<td valign='top' class=''>".$row['dialplan_uuid']."&nbsp;</td>\n";
			echo "	<td valign='top' class='".$row_style[$c]." tr_link_void' style='align: center; padding: 3px 3px 0px 8px;'>\n";
			echo "		<input type='checkbox' name=\"destinations[$x][checked]\" id='checkbox_".$x."' value='true' onclick=\"if (!this.checked) { document.getElementById('chk_all_".$x."').checked = false; }\">\n";
			echo "		<input type='hidden' name=\"destinations[$x][destination_uuid]\" value='".escape($row['destination_uuid'])."' />\n";
			echo "	</td>\n";
			if ($_GET['show'] == "all" && permission_exists('destination_all')) {
				if (strlen($_SESSION['domains'][$row['domain_uuid']]['domain_name']) > 0) {
					$domain = $_SESSION['domains'][$row['domain_uuid']]['domain_name'];
				}
				else {
					$domain = $text['label-global'];
				}
				echo "	<td valign='top' class='".$row_style[$c]."'>".escape($domain)."</td>\n";
			}
			echo "	<td valign='top' class='".$row_style[$c]."'>".escape($row['destination_type'])."&nbsp;</td>\n";
			echo "	<td valign='top' class='".$row_style[$c]."'>".escape(format_phone($row['destination_number']))."&nbsp;</td>\n";
			//echo "	<td valign='top' class='".$row_style[$c]."'>".$row['destination_number_regex']."&nbsp;</td>\n";
			echo "	<td valign='top' class='".$row_style[$c]."'>".escape($row['destination_context'])."&nbsp;</td>\n";
			//echo "	<td valign='top' class='".$row_style[$c]."'>".escape($row['fax_uuid'])."&nbsp;</td>\n";
			if (permission_exists('outbound_caller_id_select')) {
				echo "	<td valign='top' class='".$row_style[$c]."'>".escape($row['destination_caller_id_name'])."&nbsp;</td>\n";
				echo "	<td valign='top' class='".$row_style[$c]."'>".escape($row['destination_caller_id_number'])."&nbsp;</td>\n";
			}
			//echo "	<td valign='top' class='".$row_style[$c]."'>".escape($row['destination_cid_name_prefix'])."&nbsp;</td>\n";
			//echo "	<td valign='top' class='".$row_style[$c]."'>".escape($row['destination_app'])."&nbsp;</td>\n";
			//echo "	<td valign='top' class='".$row_style[$c]."'>".escape($row['destination_data'])."&nbsp;</td>\n";
			//echo "	<td valign='top' class='".$row_style[$c]."'>".escape($row['destination_record'])."&nbsp;</td>\n";
			//echo "	<td valign='top' class='".$row_style[$c]."'>".escape($row['destination_accountcode'])."&nbsp;</td>\n";
			echo "	<td valign='top' class='".$row_style[$c]."'>".escape($row['destination_enabled'])."&nbsp;</td>\n";
			echo "	<td valign='top' class='row_stylebg'>".escape($row['destination_description'])."&nbsp;</td>\n";
			echo "	<td class='list_control_icons'>";
			if (permission_exists('destination_edit')) {
				echo "<a href='destination_edit.php?id=".escape($row['destination_uuid'])."' alt='".$text['button-edit']."'>$v_link_label_edit</a>";
			}
			if (permission_exists('destination_delete')) {
				echo "<button type='submit' class='btn btn-default list_control_icon' name=\"destinations[$x][action]\" alt='".$text['button-delete']."' value='delete'><span class='glyphicon glyphicon-remove'></span></button>";
			}
			echo "	</td>\n";
			echo "</tr>\n";
			$x++;
			$c = 1 - $c;  // Switch $c = 0/1/0...
		} //end foreach
		unset($sql, $destinations, $row_count);
	} //end if results

	echo "<tr>\n";
	echo "<td colspan='10' align='left'>\n";
	echo "	<table width='100%' cellpadding='0' cellspacing='0'>\n";
	echo "	<tr>\n";
	echo "		<td width='33.3%' nowrap='nowrap'>&nbsp;</td>\n";
	echo "		<td width='33.3%' align='center' nowrap='nowrap'>$paging_controls</td>\n";
	echo "		<td class='list_control_icons'>";
	if (permission_exists('destination_add')) {
		echo 		"<a href='destination_edit.php?type=".escape($destination_type)."' alt='".$text['button-add']."'>$v_link_label_add</a>";
	}
	else {
		echo 		"&nbsp;";
	}
	echo "		</td>\n";
	echo "	</tr>\n";
 	echo "	</table>\n";
	echo "</td>\n";
	echo "</tr>\n";
	echo "</table>";
	echo "</form>\n";
	echo "<br /><br />";

//include the footer
	require_once "resources/footer.php";

?>
