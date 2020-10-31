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
	if (permission_exists('phonebook_add') || permission_exists('phonebook_edit')) {
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
		$name = check_str($_POST["name"]);
		$phonenumber = check_str($_POST["phonenumber"]);
		$phonebook_desc = check_str($_POST["phonebook_desc"]);
		$phonebook_groups = isset($_POST["phonebook_groups"]) ? $_POST["phonebook_groups"] : [''];
		
		foreach ($phonebook_groups as $k => $v) {
			$phonebook_groups[$k] = check_str($v);
		}
	}

    if (isset($_REQUEST["id"])) {
        $action = "update";
		$phonebook_uuid = check_str($_REQUEST["id"]);
    } else {
        $action = "add";
	}


    if (count($_POST) > 0 && strlen($_POST["persistformvar"]) == 0) {

    	$msg = '';

    	//check for all required data
    	if (strlen($name) == 0) { $msg .= $text['message-required'].$text['label-phonebook_name']."<br>\n"; }
    	if (strlen($phonenumber) == 0) { $msg .= $text['message-required'].$text['label-phonebook_phonenumber']."<br>\n"; }

    	if (strlen($msg) > 0 && strlen($_POST["persistformvar"]) == 0) {
    		require_once "resources/header.php";
    		require_once "resources/persist_form_var.php";
    		echo "<div align='center'>\n";
    		echo "<table><tr><td>\n";
    		echo $msg."<br />";
    		echo "</td></tr></table>\n";
    		persistformvar($_POST);
    		echo "</div>\n";
    		require_once "resources/footer.php";
    		return;
    	}

    	//add or update the database
    	if ($_POST["persistformvar"] != "true") {

    		if ($action == "add" && permission_exists('phonebook_add')) {

    			//prepare the uuids
    			$phonebook_uuid = uuid();
    			$sql = "INSERT INTO v_phonebook ";
    			$sql .= "(";
    			$sql .= "domain_uuid, ";
    			$sql .= "phonebook_uuid, ";
    			$sql .= "name, ";
    			$sql .= "phonenumber, ";
    			$sql .= "phonebook_desc";
    			$sql .= ") ";
    			$sql .= "VALUES ";
    			$sql .= "(";
				$sql .= "'$domain_uuid', ";
				$sql .= "'$phonebook_uuid', ";
    			$sql .= "'".$name."', ";
    			$sql .= "'$phonenumber', ";
    			$sql .= "'$phonebook_desc'";
				$sql .= ")";
				$db->exec(check_sql($sql));
				unset($sql);

				foreach ($phonebook_groups as $phonebook_group) {
					if (strlen($phonebook_group) > 0) {
						$sql = "INSERT INTO v_phonebook_to_groups (";
						$sql .= "domain_uuid, ";
						$sql .= "phonebook_uuid, ";
						$sql .= "group_uuid) ";
						$sql .= "VALUES ";
						$sql .= "('$domain_uuid', ";
						$sql .= "'$phonebook_uuid', ";
						$sql .= "'".$phonebook_group."')";
						$db->exec(check_sql($sql));
						unset($sql);
					}
				}

				// Set action to update to provide phonebook_uuid to next iteration
				$action = "update";

    		 //if ($action == "add")
			} elseif ($action == "update" && permission_exists('phonebook_edit')) {

    			$sql = "UPDATE v_phonebook SET ";
    			$sql .= "name = '$name', ";
    			$sql .= "phonenumber = '$phonenumber', ";
    			$sql .= "phonebook_desc = '$phonebook_desc' ";
    			$sql .= "WHERE domain_uuid = '$domain_uuid' ";
				$sql .= "AND phonebook_uuid = '$phonebook_uuid'";
				$prep_statement = $db->prepare(check_sql($sql));
				$prep_statement->execute();
				unset($prep_statement, $sql);
				
				// Add group relations

				$sql = "DELETE FROM v_phonebook_to_groups WHERE";
       			$sql .= " domain_uuid = '$domain_uuid' AND";
				$sql .= " phonebook_uuid = '$phonebook_uuid'";
				$prep_statement = $db->prepare(check_sql($sql));
				$prep_statement->execute();
				unset($prep_statement, $sql);

				foreach ($phonebook_groups as $phonebook_group) {
					if (strlen($phonebook_group) > 0) {
						$sql = "INSERT INTO v_phonebook_to_groups (";
						$sql .= "domain_uuid, ";
						$sql .= "phonebook_uuid, ";
						$sql .= "group_uuid) ";
						$sql .= "VALUES ";
						$sql .= "('$domain_uuid', ";
						$sql .= "'$phonebook_uuid', ";
						$sql .= "'".$phonebook_group."')";
						$prep_statement = $db->prepare(check_sql($sql));
						$prep_statement->execute();
						unset($prep_statement, $sql);
					}
				} // End adding phonebook to group relations

    		} //if ($action == "update")

			if (isset($_REQUEST['is_updated']) || isset($_REQUEST['is_added'])) {
				//redirect the browser
				$_SESSION["message"] = isset($_REQUEST['is_updated']) ? $text['label-update-complete'] : $text['label-add-complete'];
				header("Location:  phonebook.php");
				return;
			}

    	} //if ($_POST["persistformvar"] != "true")
	} // if (count($_POST) > 0 && strlen($_POST["persistformvar"]) == 0)


	// Get group list
	$sql = "SELECT * FROM v_phonebook_groups";
	$sql .= " WHERE domain_uuid = '$domain_uuid'";
	$prep_statement = $db->prepare(check_sql($sql));
	$prep_statement->execute();
	$phonebook_groups_all = $prep_statement->fetchAll();
	unset ($prep_statement, $sql);

	//pre-populate the form. To be honest - quite strange way to populate the form. As we should ALWAYS get only 1 result.
	if (count($_GET) > 0 && $_POST["persistformvar"] != "true") {
		$group_id = check_str($_GET["id"]);
		$sql = "SELECT * FROM v_phonebook ";
		$sql .= "WHERE domain_uuid = '$domain_uuid' ";
		$sql .= "AND phonebook_uuid = '$phonebook_uuid' ";
		$prep_statement = $db->prepare(check_sql($sql));
		$prep_statement->execute();
		$result = $prep_statement->fetchAll();
		foreach ($result as &$row) {
            //set the php variables
            $name = $row["name"];
			$phonenumber = $row["phonenumber"];
			$phonebook_desc = $row["phonebook_desc"];
		}
		unset ($prep_statement, $sql);

		// Get group list for this entry
		$sql = "SELECT group_uuid FROM v_phonebook_to_groups ";
		$sql .= "WHERE domain_uuid = '$domain_uuid' ";
		$sql .= "AND phonebook_uuid = '$phonebook_uuid' ";
		$prep_statement = $db->prepare(check_sql($sql));
		$prep_statement->execute();
		$result = $prep_statement->fetchAll();

		$phonebook_groups = array();
		foreach ($result as $row) {
			$phonebook_groups[] = $row['group_uuid'];
		}
		$phonebook_groups_orig = $phonebook_groups;
		unset ($prep_statement, $sql);
	}

	// Show the content
	//show the header
	require_once "resources/header.php";
	if ($action == "update") {
		$document['title'] = $text['title-phonebook-edit'];
	}
	if ($action == "add") {
		$document['title'] = $text['title-phonebook-add'];

		//echo json_encode($_REQUEST);
		//die(0);
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
		echo escape($text['header-phonebook-edit']);
	}
	if ($action == "add") {
		echo escape($text['header-phonebook-add']);
	}

	echo "</b></td>\n";
	echo "<td width='70%' align='right'>";
	echo "	<input type='button' class='btn' name='' alt='".$text['button-back']."' onclick=\"window.location='phonebook.php'\" value='".$text['button-back']."'>";
	echo "	<input type='submit' name='submit' class='btn' value='".$text['button-save']."'>\n";
	echo "</td>\n";
	echo "</tr>\n";

    // Name
	echo "<tr>\n";
	echo "<td class='vncellreq' valign='top' align='left' nowrap='nowrap'>\n";
	echo "	".$text['label-phonebook_name']."\n";
	echo "</td>\n";
	echo "<td class='vtable' align='left'>\n";
	echo "	<input class='formfld' type='text' name='name' maxlength='255' value=\"" . escape($name) . "\">\n";
	echo "<br />\n";
	echo $text['description-phonebook_name']."\n";
	echo "</td>\n";
	echo "</tr>\n";

    // Number
    echo "<tr>\n";
    echo "<td class='vncellreq' valign='top' align='left' nowrap='nowrap'>\n";
    echo "  ".$text['label-phonebook_phonenumber']."\n";
    echo "</td>\n";
    echo "<td class='vtable' align='left'>\n";
    echo "  <input class='formfld' type='text' name='phonenumber' maxlength='255' value=\"" . escape($phonenumber) . "\">\n";
    echo "<br />\n";
    echo $text['description-phonebook_phonenumber']."\n";
    echo "</td>\n";
	echo "</tr>\n";
	
	// Description
    echo "<tr>\n";
    echo "<td class='vncellreq' valign='top' align='left' nowrap='nowrap'>\n";
    echo "  ".$text['label-phonebook_desc']."\n";
    echo "</td>\n";
    echo "<td class='vtable' align='left'>\n";
    echo "  <input class='formfld' type='text' name='phonebook_desc' maxlength='255' value=\"" . escape($phonebook_desc) . "\">\n";
    echo "<br />\n";
    echo $text['description-phonebook_desc']."\n";
    echo "</td>\n";
    echo "</tr>\n";
	// Checkboxes

	echo "<tr><td colspan='2'><br /></td></tr>\n";


    echo "<tr>\n";
    echo "<td class='vncellreq' valign='top' align='left'>\n";
    echo "  ".$text['label-phonebook_groups'];
    echo "</td>\n";
	echo "<td class='row_style1'>\n";
    echo "<br />\n";
    echo $text['description-phonebook_groups']."\n";
    echo "</td>\n";
    echo "</tr>\n";
	
	foreach ($phonebook_groups_all as $phonebook_group) {

		$phonebook_group = array_map("escape", $phonebook_group);

		$checked = in_array($phonebook_group['group_uuid'], $phonebook_groups) ? "checked" : "";

		$on_click_text = "(document.getElementById('group_" . $phonebook_group['group_uuid'] ."').checked)" ;
		$on_click_text .= " ? document.getElementById('group_" . $phonebook_group['group_uuid'] . "').checked = false";
		$on_click_text .= " : document.getElementById('group_". $phonebook_group['group_uuid'] . "').checked = true;";

		echo "<tr id='permission_" . $phonebook_group['group_uuid'] . "'>\n";
		echo "	<td valign='top' style='text-align: right;' nowrap='nowrap' class='".$row_style[$c]."' onclick=\"".$on_click_text."\">" .$phonebook_group['group_name'] . "</td>\n";
		echo "	<td valign='top' class='".$row_style[$c]."' style='text-align: left; vertical-align:middle;'><input type='checkbox' name='phonebook_groups[]' id='group_" . $phonebook_group['group_uuid'] ."' ".$checked." value='" . $phonebook_group['group_uuid'] . "'>   " . $phonebook_group['group_desc'] . "&nbsp;</td>\n";
		echo "</tr>\n";
		$c = 1 - $c; // Switch c = 0/1/0/1....
	}

	echo "	<tr>\n";
	echo "		<td colspan='2' align='right'>\n";
	if ($action == "update") {
		echo "		<input type='hidden' name='id' value='" . escape($phonebook_uuid) . "'>\n";
		echo "		<input type='hidden' name='is_updated' value='yes'>\n";
	} elseif ($action == "add") {
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
