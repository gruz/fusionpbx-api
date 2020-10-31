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
	Lewis Hallam <lewishallam80@gmail.com>
*/

//includes
	require_once "root.php";
	require_once "resources/require.php";
	require_once "resources/check_auth.php";

//check permissions
	if (permission_exists('phonebook_group_add') || permission_exists('phonebook_group_edit')) {
		//access granted
	} else {
		echo "access denied";
		exit;
	}

//add multi-lingual support
	$language = new text;
	$text = $language->get();

//action add, update or load from server


    $c = 0;
    $row_style["0"] = "row_style0";
	$row_style["1"] = "row_style1";
	

//get http post variables and set them to php variables
	if (count($_POST) > 0) {
		//set the variables from the http values
		$group_name = check_str($_POST["group_name"]);
		$group_desc = check_str($_POST["group_desc"]);
	}

    if (isset($_REQUEST["id"])) {
        $action = "update";
		$group_id = check_str($_REQUEST["id"]);
    } else {
        $action = "add";
	}


    if (count($_POST) > 0 && strlen($_POST["persistformvar"]) == 0) {

    	$msg = '';

    	//check for all required data
    	if (strlen($group_name) == 0) { $msg .= $text['message-required'].$text['label-phonebook_group_name']."<br>\n"; }

    	if (strlen($msg) > 0 && strlen($_POST["persistformvar"]) == 0) {
    		require_once "resources/header.php";
    		require_once "resources/persist_form_var.php";
    		echo "<div align='center'>\n";
    		echo "<table><tr><td>\n";
    		echo escape($msg) . "<br />";
    		echo "</td></tr></table>\n";
    		persistformvar($_POST);
    		echo "</div>\n";
    		require_once "resources/footer.php";
    		return;
    	}

    	//add or update the database
    	if ($_POST["persistformvar"] != "true") {

    		if ($action == "add" && permission_exists('phonebook_group_add')) {

    			//prepare the uuids
    			$group_id = uuid();
    			$sql = "INSERT INTO v_phonebook_groups ";
    			$sql .= "(";
    			$sql .= "domain_uuid, ";
    			$sql .= "group_uuid, ";
    			$sql .= "group_name, ";
    			$sql .= "group_desc";
    			$sql .= ") ";
    			$sql .= "VALUES ";
    			$sql .= "(";
				$sql .= "'$domain_uuid', ";
				$sql .= "'$group_id', ";
    			$sql .= "'".$group_name."', ";
    			$sql .= "'$group_desc'";
				$sql .= ")";
    			$db->exec(check_sql($sql));
    			unset($sql);
    		} //if ($action == "add")

    		if ($action == "update" && permission_exists('phonebook_group_edit')) {

    			$sql = "UPDATE v_phonebook_groups SET ";
    			$sql .= "group_name = '$group_name', ";
    			$sql .= "group_desc = '$group_desc' ";
    			$sql .= "WHERE domain_uuid = '$domain_uuid' ";
				$sql .= "AND group_uuid = '$group_id'";
    			$db->exec(check_sql($sql));
				unset($sql);

			} //if ($action == "update")
			
			if (isset($_REQUEST['is_updated']) || isset($_REQUEST['is_added'])) {
				//redirect the browser
				$_SESSION["message"] = escape(isset($_REQUEST['is_updated']) ? $text['label-update-complete'] : $text['label-add-complete']);
				header("Location: groups.php");
				return;
			}

    	} //if ($_POST["persistformvar"] != "true")
	} // if (count($_POST) > 0 && strlen($_POST["persistformvar"]) == 0)
	

//pre-populate the form. To be honest - quite strange way to populate the form. As we should ALWAYS get only 1 result.
	if (count($_GET) > 0 && $_POST["persistformvar"] != "true") {
		$group_id = check_str($_GET["id"]);
		$sql = "SELECT * FROM v_phonebook_groups ";
		$sql .= "WHERE domain_uuid = '$domain_uuid' ";
		$sql .= "AND group_uuid = '$group_id' ";
		$prep_statement = $db->prepare(check_sql($sql));
		$prep_statement->execute();
		$result = $prep_statement->fetchAll();
		foreach ($result as &$row) {
            //set the php variables
            $group_name = $row["group_name"];
            $group_desc = $row["group_desc"];
		}
		unset ($prep_statement);
	}

//show the header
	require_once "resources/header.php";
	if ($action == "update") {
		$document['title'] = escape($text['title-phonebook_groups-edit']);
	}
	if ($action == "add") {
		$document['title'] = escape($text['title-phonebook_groups-add']);
	}

	/*
	if (if_group("superadmin")) {
		echo "<script>\n";
		echo "var Objs;\n";
		echo "\n";
		echo "function changeToInput(obj){\n";
		echo "	tb=document.createElement('INPUT');\n";
		echo "	tb.type='text';\n";
		echo "	tb.name=obj.name;\n";
		echo "	tb.setAttribute('class', 'formfld');\n";
		//echo "	tb.setAttribute('style', 'width: 380px;');\n";
		echo "	tb.value=obj.options[obj.selectedIndex].value;\n";
		echo "	tbb=document.createElement('INPUT');\n";
		echo "	tbb.setAttribute('class', 'btn');\n";
		echo "	tbb.setAttribute('style', 'margin-left: 4px;');\n";
		echo "	tbb.type='button';\n";
		echo "	tbb.value=$('<div />').html('&#9665;').text();\n";
		echo "	tbb.objs=[obj,tb,tbb];\n";
		echo "	tbb.onclick=function(){ Replace(this.objs); }\n";
		echo "	obj.parentNode.insertBefore(tb,obj);\n";
		echo "	obj.parentNode.insertBefore(tbb,obj);\n";
		echo "	obj.parentNode.removeChild(obj);\n";
		echo "}\n";
		echo "\n";
		echo "function Replace(obj){\n";
		echo "	obj[2].parentNode.insertBefore(obj[0],obj[2]);\n";
		echo "	obj[0].parentNode.removeChild(obj[1]);\n";
		echo "	obj[0].parentNode.removeChild(obj[2]);\n";
		echo "}\n";
		echo "</script>\n";
		echo "\n";
	}
	*/

//show the content
	echo "<form method='post' name='frm' action=''>\n";
	echo "<table width='100%' border='0' cellpadding='0' cellspacing='0'>\n";
	echo "<tr>\n";
	echo "<td align='left' width='30%' nowrap='nowrap'><b>";
	if ($action == "update") {
		echo escape($text['header-phonebook_groups-edit']);
	}
	if ($action == "add") {
		echo escape($text['header-phonebook_groups-add']);
	}

	echo "</b></td>\n";
	echo "<td width='70%' align='right'>";
	echo "	<input type='button' class='btn' name='' alt='".$text['button-back']."' onclick=\"window.location='groups.php'\" value='".$text['button-back']."'>";
	echo "	<input type='submit' name='submit' class='btn' value='".$text['button-save']."'>\n";
	echo "</td>\n";
	echo "</tr>\n";

    // Group Name
	echo "<tr>\n";
	echo "<td class='vncellreq' valign='top' align='left' nowrap='nowrap'>\n";
	echo "	" . escape($text['label-phonebook_group_name']) . "\n";
	echo "</td>\n";
	echo "<td class='vtable' align='left'>\n";
	echo "	<input class='formfld' type='text' name='group_name' maxlength='255' value=\"" . escape($group_name) . "\">\n";
	echo "<br />\n";
	echo escape($text['description-phonebook_group_name']) . "\n";
	echo "</td>\n";
	echo "</tr>\n";

    // Group Description
    echo "<tr>\n";
    echo "<td class='vncellreq' valign='top' align='left' nowrap='nowrap'>\n";
    echo "  " . escape($text['label-phonebook_group_desc']) . "\n";
    echo "</td>\n";
    echo "<td class='vtable' align='left'>\n";
    echo "  <input class='formfld' type='text' name='group_desc' maxlength='255' value=\"" . escape($group_desc) . "\">\n";
    echo "<br />\n";
    echo escape($text['description-phonebook_group_desc']) . "\n";
    echo "</td>\n";
    echo "</tr>\n";

    

	echo "	<tr>\n";
	echo "		<td colspan='2' align='right'>\n";
	if ($action == "update") {
		echo "		<input type='hidden' name='is_updated' value='yes'>\n";
		echo "		<input type='hidden' name='id' value='" . escape($group_id) . "'>\n";
	}
	elseif ($action == "add") {
		echo "		<input type='hidden' name='is_added' value='yes'>\n";
	}
	echo "			<br>";
	echo "			<input type='submit' name='submit' class='btn' value='".$text['button-save']."'>\n";
	echo "		</td>\n";
	echo "	</tr>";
	echo "</table>";
	echo "<br><br>";
	echo "</form>";

//include the footer
	require_once "resources/footer.php";
?>
