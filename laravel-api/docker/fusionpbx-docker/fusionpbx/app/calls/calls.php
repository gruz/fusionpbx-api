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
	Portions created by the Initial Developer are Copyright (C) 2008-2017
	the Initial Developer. All Rights Reserved.

	Contributor(s):
	Mark J Crane <markjcrane@fusionpbx.com>
*/

//includes
	include "root.php";
	require_once "resources/require.php";
	require_once "resources/check_auth.php";

//check permissions
	if (permission_exists('follow_me') || permission_exists('call_forward') || permission_exists('do_not_disturb')) {
		//access granted
	}
	else {
		echo "access denied";
		exit;
	}

//get the domain_uuid from the session
	$domain_uuid = $_SESSION['domain_uuid'];

//handle search term
	$search = check_str($_GET["search"]);
	if (strlen($search) > 0) {
		$sql_mod = "and ( ";
		$sql_mod .= "extension like '%".$search."%' ";
		$sql_mod .= "or description like '%".$search."%' ";
		$sql_mod .= ") ";
	}

//add multi-lingual support
	$language = new text;
	$text = $language->get($_SESSION['domain']['language']['code'], 'app/calls');

//begin the content
	require_once "resources/header.php";
	require_once "resources/paging.php";

//define select count query
	$sql = "select count(extension_uuid) as count from v_extensions ";
	$sql .= "where domain_uuid = '".$domain_uuid."' ";
	$sql .= "and enabled = 'true' ";
	if (!(if_group("admin") || if_group("superadmin"))) {
		if (count($_SESSION['user']['extension']) > 0) {
			$sql .= "and (";
			$x = 0;
			foreach($_SESSION['user']['extension'] as $row) {
				if ($x > 0) { $sql .= "or "; }
				$sql .= "extension = '".$row['user']."' ";
				$x++;
			}
			$sql .= ")";
		}
		else {
			//used to hide any results when a user has not been assigned an extension
			$sql .= "and extension = 'disabled' ";
		}
	}
	$sql .= $sql_mod; //add search mod from above
	$prep_statement = $db->prepare(check_sql($sql));
	$prep_statement->execute();
	$row = $prep_statement->fetch(PDO::FETCH_NAMED);
	$result_count = $row['count'];
	unset ($prep_statement, $row);

	if ($is_included) {
		$rows_per_page = 10;
	}
	else {
		$rows_per_page = ($_SESSION['domain']['paging']['numeric'] != '') ? $_SESSION['domain']['paging']['numeric'] : 50;
	}
	$param = "&search=".$search;
	$page = $_GET['page'];
	if (strlen($page) == 0) { $page = 0; $_GET['page'] = 0; }
	list($paging_controls_mini, $rows_per_page, $var_3) = paging($result_count, $param, $rows_per_page, true);
	list($paging_controls, $rows_per_page, $var_3) = paging($result_count, $param, $rows_per_page);
	$offset = $rows_per_page * $page;

//select the extensions
	$sql = "select * from v_extensions ";
	$sql .= "where domain_uuid = '".$domain_uuid."' ";
	$sql .= "and enabled = 'true' ";
	if (!(if_group("admin") || if_group("superadmin"))) {
		if (count($_SESSION['user']['extension']) > 0) {
			$sql .= "and (";
			$x = 0;
			foreach($_SESSION['user']['extension'] as $row) {
				if ($x > 0) { $sql .= "or "; }
				$sql .= "extension = '".$row['user']."' ";
				$x++;
			}
			$sql .= ")";
		}
		else {
			//used to hide any results when a user has not been assigned an extension
			$sql .= "and extension = 'disabled' ";
		}
	}
	$sql .= $sql_mod; //add search mod from above
	$sql .= ' order by extension asc';
	$sql .= " limit ".$rows_per_page." offset ".$offset." ";
	$prep_statement = $db->prepare(check_sql($sql));
	$prep_statement->execute();
	$extensions = $prep_statement->fetchAll(PDO::FETCH_NAMED);
	unset ($prep_statement, $sql);

//set the row style
	$c = 0;
	$row_style["0"] = "row_style0";
	$row_style["1"] = "row_style1";

//start the content
	echo "<table cellpadding='0' cellspacing='0' border='0' width='100%'>\n";
	echo "	<tr>\n";
	echo "		<td valign='top'>";
	echo "			<b>".$text['header-call_routing']."</b><br />";
	echo "		</td>\n";
	echo "		<td valign='top' style='text-align: right; white-space: nowrap;'>\n";
	if ($result_count > 10 && $is_included) {
		echo "			<input id='btn_viewall_callrouting' type='button' class='btn' value='".$text['button-view_all']."' onclick=\"document.location.href='".PROJECT_PATH."/app/calls/calls.php';\">";
	}
	if (!$is_included) {
		echo "				<form method='get' action='' style='display: inline-block;'>\n";
		echo "				<input type='text' class='txt' style='width: 150px' name='search' value='".escape($search)."'>";
		echo "				<input type='submit' class='btn' name='submit' value='".$text['button-search']."'>";
		echo "				</form>\n";
		if ($paging_controls_mini != '') {
			echo 			"<span style='margin-left: 15px;'>".$paging_controls_mini."</span>\n";
		}
	}	echo "		</td>\n";
	echo "	</tr>\n";
	echo "	<tr>\n";
	echo "		<td colspan='2' valign='top'>";
	if (!$is_included) {
		echo $text['description-call_routing']."<br />";
	}
	echo "		</td>\n";
	echo "	</tr>\n";
	echo "</table>\n";
	echo "<br />";

	echo "<table class='tr_hover' width='100%' border='0' cellpadding='0' cellspacing='0'>\n";
	echo "<tr>\n";
	echo "<th>".$text['table-extension']."</th>\n";
	if (permission_exists('call_forward')) { echo "<th>".$text['label-call-forward']."</th>\n"; }
	if (permission_exists('follow_me')) { echo "<th>".$text['label-follow-me']."</th>\n"; }
	if (permission_exists('do_not_disturb')) { echo "<th>".$text['label-dnd']."</th>\n"; }
	echo "<th>".$text['label-description']."</th>\n";
	echo "	<td class='list_control_icon'>&nbsp;</td>\n";
	echo "</tr>\n";

	if (is_array($extensions)) {
		foreach($extensions as $row) {
			$tr_url = PROJECT_PATH."/app/calls/call_edit.php?id=".$row['extension_uuid']."&return_url=".urlencode($_SERVER['REQUEST_URI']);
			$tr_link = (permission_exists('call_forward') || permission_exists('follow_me') || permission_exists('do_not_disturb')) ? "href='".$tr_url."'" : null;
			echo "<tr ".$tr_link.">\n";
			echo "	<td valign='top' class='".$row_style[$c]."'><a ".$tr_link.">".escape($row['extension'])."</a></td>\n";
			if (permission_exists('call_forward')) {
				if (($row['forward_all_enabled'] == 'true') || ($row['forward_no_answer_enabled'] == 'true') || ($row['forward_user_not_registered_enabled'] == 'true') || ($row['forward_busy_enabled'] == 'true')) {
					$call_forward_text = "(";
					$call_forward_text .= ($row['forward_all_enabled'] == 'true') ? "A: ".escape(format_phone($row['forward_all_destination']))." " : "";
					$call_forward_text .= ($row['forward_busy_enabled'] == 'true') ? "B: ".escape(format_phone($row['forward_busy_destination']))." " : "";
					$call_forward_text .= ($row['forward_no_answer_enabled'] == 'true') ? "NA: ".escape(format_phone($row['forward_no_answer_destination']))." " : "";
					$call_forward_text .= ($row['forward_user_not_registered_enabled'] == 'true') ? "NR: ".escape(format_phone($row['forward_user_not_registered_destination'])) : "";
					$call_forward_text = trim($call_forward_text);
					$call_forward_text .= ")";
				} else {
					$call_forward_text = '&nbsp;';
				}
				echo "	<td valign='top' class='".$row_style[$c]."'>" . $call_forward_text . "</td>";
			}
			if (permission_exists('follow_me')) {
				if ($row['follow_me_uuid'] != '') {
					//check if follow me is enabled
						$sql = "select follow_me_enabled from v_follow_me where follow_me_uuid = '".$row['follow_me_uuid']."' and domain_uuid = '".$domain_uuid."'";
						$prep_statement = $db->prepare(check_sql($sql));
						$prep_statement->execute();
						$row_x = $prep_statement->fetch(PDO::FETCH_NAMED);
						$follow_me_enabled = ($row_x['follow_me_enabled'] == 'true') ? true : false;
						unset($sql, $prep_statement, $row_x);
					//get destination count if enabled
						if ($follow_me_enabled) {
							$sql = "select count(follow_me_destination_uuid) as destination_count from v_follow_me_destinations where follow_me_uuid = '".$row['follow_me_uuid']."' and domain_uuid = '".$domain_uuid."'";
							$prep_statement = $db->prepare(check_sql($sql));
							$prep_statement->execute();
							$row_x = $prep_statement->fetch(PDO::FETCH_NAMED);
							$follow_me_destination_count = $row_x['destination_count'];
							unset($sql, $prep_statement, $row_x);
						}
				}
				else {
					$follow_me_enabled = false;
				}
				echo "	<td valign='top' class='".$row_style[$c]."'>".(($follow_me_enabled) ? $text['label-enabled']." (".$follow_me_destination_count.")" : '&nbsp;')."</td>";
			}
			if (permission_exists('do_not_disturb')) {
				echo "	<td valign='top' class='".$row_style[$c]."'>".(($row['do_not_disturb'] == 'true') ? $text['label-enabled'] : '&nbsp;')."</td>";
			}
			echo "	<td valign='top' class='".$row_style[$c]."'>".escape($row['description'])."&nbsp;</td>\n";

			echo "	<td class='list_control_icon'><a href='".$tr_url."' alt='".$text['button-edit']."'>".$v_link_label_edit."</a></td>\n";
			echo "</tr>\n";
			$c = ($c) ? 0 : 1;
		} //end foreach
		unset($sql, $extensions);
	} //end if results

	echo "</table>";
	echo "<br />";

	if (strlen($paging_controls) > 0 && (!$is_included)) {
		echo "<center>".$paging_controls."</center>\n";
		echo "<br /><br />\n";
	}

	if (!$is_included) {
		echo "<br />";
		require_once "resources/footer.php";
	}

?>
