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
	include "root.php";
	require_once "resources/require.php";
	require_once "resources/check_auth.php";

//check permissions
	if (permission_exists('module_view')) {
		//access granted
	}
	else {
		echo "access denied";
		exit;
	}

//add multi-lingual support
	$language = new text;
	$text = $language->get();

//get includes and the title
	require_once "resources/header.php";
	$document['title'] = $text['title-modules'];
	require_once "resources/paging.php";

//get the http values ans set as variables
	$order_by = $_GET["order_by"];
	$order = $_GET["order"];

//start or stop a module
	$fp = event_socket_create($_SESSION['event_socket_ip_address'], $_SESSION['event_socket_port'], $_SESSION['event_socket_password']);
	if (strlen($_GET["a"]) > 0) {
		if ($_GET["a"] == "stop") {
			$module_name = $_GET["m"];
			if ($fp) {
				$cmd = "api unload $module_name";
				$response = trim(event_socket_request($fp, $cmd));
				$msg = "<strong>".$text['label-unload_module'].":</strong> <pre>".$response."</pre>";
			}
		}
		if ($_GET["a"] == "start") {
			$module_name = $_GET["m"];
			if ($fp) {
				$cmd = "api load $module_name";
				$response = trim(event_socket_request($fp, $cmd));
				$msg = "<strong>".$text['label-load_module'].":</strong> <pre>".$response."</pre>";
			}
		}
	}

//check connection status
	$esl_alive = false;
	if($fp){
		$esl_alive = true;
		fclose($fp);
	}

//Warning if FS not start
	if(!$esl_alive){
		$msg = "<div align='center'>".$text['error-event-socket']."<br /></div>";
		echo "<div align='center'>\n";
		echo "<table width='40%'>\n";
		echo "<tr>\n";
		echo "<th align='left'>".$text['label-message']."</th>\n";
		echo "</tr>\n";
		echo "<tr>\n";
		echo "<td class='row_style1'><strong>$msg</strong></td>\n";
		echo "</tr>\n";
		echo "</table>\n";
		echo "</div>\n";
	}

//use the module class to get the list of modules from the db and add any missing modules
	$module = new modules;
	$module->db = $db;
	$module->dir = $_SESSION['switch']['mod']['dir'];
	$module->get_modules();
	$result = $module->modules;
	$module_count = count($result);
	$module->synch();
	$module->xml();
	$msg = $module->msg;

//show the msg
	if ($msg) {
		echo "<div align='center'>\n";
		echo "<table width='40%'>\n";
		echo "<tr>\n";
		echo "<th align='left'>".$text['label-message']."</th>\n";
		echo "</tr>\n";
		echo "<tr>\n";
		echo "<td class='row_style1'>$msg</td>\n";
		echo "</tr>\n";
		echo "</table>\n";
		echo "</div>\n";
	}

//show the content
	echo "<table width='100%' cellpadding='0' cellspacing='0' border='0'><tr>\n";
	echo "<td align='left' nowrap><b>".$text['header-modules']."</b></td>\n";
	echo "</tr>\n";
	echo "<tr>\n";
	echo "<td align='left'>".$text['description-modules']."</td>\n";
	echo "</tr>\n";
	echo "</table>\n";

	$c = 0;
	$row_style["0"] = "row_style0";
	$row_style["1"] = "row_style1";

	echo "<table class='tr_hover' width='100%' border='0' cellpadding='0' cellspacing='0'>\n";
	$tmp_module_header = "\n";
	$tmp_module_header .= "<tr>\n";
	$tmp_module_header .= "<th>".$text['label-label']."</th>\n";
	$tmp_module_header .= "<th>".$text['label-status']."</th>\n";
	$tmp_module_header .= "<th>".$text['label-action']."</th>\n";
	$tmp_module_header .= "<th>".$text['label-enabled']."</th>\n";
	$tmp_module_header .= "<th>".$text['label-description']."</th>\n";
	$tmp_module_header .= "<td class='list_control_icons'>";
	$tmp_module_header .= "<a href='module_edit.php' alt='".$text['button-add']."'>$v_link_label_add</a>";
	$tmp_module_header .= "</td>\n";
	$tmp_module_header .= "<tr>\n";

	if ($module_count > 0) {
		$prev_module_category = '';
		foreach($result as $row) {
			if ($prev_module_category != $row["module_category"]) {
				$c=0;
				if (strlen($prev_module_category) > 0) {
					echo "<tr>\n";
					echo "<td colspan='6'>\n";
					echo "	<table width='100%' cellpadding='0' cellspacing='0'>\n";
					echo "	<tr>\n";
					echo "		<td width='33.3%' nowrap>&nbsp;</td>\n";
					echo "		<td width='33.3%' align='center' nowrap>&nbsp;</td>\n";
					echo "		<td width='33.3%' align='right'>\n";
					if (permission_exists('module_add')) {
						echo "			<a href='module_edit.php' alt='add'>$v_link_label_add</a>\n";
					}
					echo "		</td>\n";
					echo "	</tr>\n";
					echo "	</table>\n";
					echo "</td>\n";
					echo "</tr>\n";
				}
				echo "<tr><td colspan='4' align='left'>\n";
				echo "	<br />\n";
				echo "	<br />\n";
				echo "	<b>".$row["module_category"]."</b>&nbsp;</td></tr>\n";
				echo $tmp_module_header;
			}

			$tr_link = (permission_exists('module_edit')) ? "href='module_edit.php?id=".escape($row["module_uuid"])."'" : null;
			echo "<tr ".$tr_link.">\n";
			echo "   <td valign='top' class='".$row_style[$c]."'>";
			if (permission_exists('module_edit')) {
				echo "<a href='module_edit.php?id=".escape($row["module_uuid"])."'>".escape($row["module_label"])."</a>";
			}
			else {
				echo $row["module_label"];
			}
			echo "	</td>\n";
			if($esl_alive) {
				if ($module->active($row["module_name"])) {
					echo "   <td valign='top' class='".$row_style[$c]."'>".$text['label-running']."</td>\n";
					echo "   <td valign='top' class='".$row_style[$c]."'><a href='modules.php?a=stop&m=".escape($row["module_name"])."' alt='".$text['label-stop']."'>".$text['label-stop']."</a></td>\n";
				}
				else {
					if ($row['module_enabled']=="true") {
						echo "   <td valign='top' class='".$row_style[$c]."'><b>".$text['label-stopped']."</b></td>\n";
					}
					else {
						echo "   <td valign='top' class='".$row_style[$c]."'>".$text['label-stopped']." ".escape($notice)."</td>\n";
					}
					echo "   <td valign='top' class='".$row_style[$c]."'><a href='modules.php?a=start&m=".escape($row["module_name"])."' alt='".$text['label-start']."'>".$text['label-start']."</a></td>\n";
				}
			}
			else{
				echo "   <td valign='top' class='".$row_style[$c]."'>".$text['label-unknown']."</td>\n";
				echo "   <td valign='top' class='".$row_style[$c]."'>".$text['label-none']."</td>\n";
			}
			echo "   <td valign='top' class='".$row_style[$c]."'>";
			if ($row["module_enabled"] == "true") {
				echo $text['option-true'];
			}
			else if ($row["module_enabled"] == "false") {
				echo $text['option-false'];
			}
			echo "</td>\n";
			echo "	<td valign='top' class='row_stylebg'>".escape($row["module_description"])."&nbsp;</td>\n";
			echo "   <td class='list_control_icons'>";
			if (permission_exists('module_edit')) {
				echo "<a href='module_edit.php?id=".escape($row["module_uuid"])."' alt='".$text['button-edit']."'>$v_link_label_edit</a>";
			}
			if (permission_exists('module_delete')) {
				echo "<a href='module_delete.php?id=".escape($row["module_uuid"])."' alt='".$text['button-delete']."' onclick=\"return confirm('".$text['confirm-delete']."')\">$v_link_label_delete</a>";
			}
			echo "</td>\n";
			echo "</tr>\n";

			$prev_module_category = $row["module_category"];
			$c = 1 - $c;  // Switch $c = 0/1/0...
		} //end foreach
		unset($sql, $modules, $row_count);
	} //end if results

	echo "<tr>\n";
	echo "<td colspan='6'>\n";
	echo "	<table width='100%' cellpadding='0' cellspacing='0'>\n";
	echo "	<tr>\n";
	echo "		<td width='33.3%' nowrap>&nbsp;</td>\n";
	echo "		<td width='33.3%' align='center' nowrap>$paging_controls</td>\n";
	echo "		<td class='list_control_icons'>";
	if (permission_exists('module_add')) {
		echo "<a href='module_edit.php' alt='".$text['button-add']."'>$v_link_label_add</a>";
	}
	echo "</td>\n";
	echo "	</tr>\n";
	echo "	</table>\n";
	echo "</td>\n";
	echo "</tr>\n";

	echo "</table>";
	echo "<br><br>";

//show the footer
	require_once "resources/footer.php";

?>
