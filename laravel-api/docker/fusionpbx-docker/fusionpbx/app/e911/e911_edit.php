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

	require_once "app/e911/api_calls.php";

//check permissions
	if (permission_exists('e911_add') || permission_exists('e911_edit')) {
		//access granted
	}
	else {
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
		$e911_did = check_str($_POST["e911_did"]);
		$e911_address_1 = check_str($_POST["e911_address_1"]);
		$e911_address_2 = check_str($_POST["e911_address_2"]);
		$e911_city = check_str($_POST["e911_city"]);
		$e911_state = check_str($_POST["e911_state"]);
		$e911_zip = check_str($_POST["e911_zip"]);
		$e911_zip_4 = check_str($_POST["e911_zip_4"]);
		$e911_callername = check_str($_POST["e911_callername"]);
		$e911_alert_email_enable = check_str($_POST["e911_alert_email_enable"]);
		$e911_alert_email = check_str($_POST["e911_alert_email"]);
		$e911_validated = check_str($_POST["e911_validated"]);
	}

	$e911_success_got_from_server = False;
    if (isset($_REQUEST["update_from_server"]) && isset($_REQUEST["e911_did"])) {
        // TODO - override data with API request
        $e911_did = check_str($_REQUEST["e911_did"]);
        $e911_request_data = query_e911_data($e911_did);
        if ($e911_request_data) {
			$e911_success_got_from_server = True;

            $e911_address_1 = $e911_request_data['e911_address_1'];
            $e911_address_2 = $e911_request_data['e911_address_2'];
            $e911_city = $e911_request_data['e911_city'];
            $e911_state = $e911_request_data['e911_state'];
            $e911_zip = $e911_request_data['e911_zip'];
            $e911_zip_4 = $e911_request_data['e911_zip_4'];
            $e911_callername = $e911_request_data['e911_callername'];
            $e911_validated = check_str($e911_request_data['e911_validated']);
        } else {
            $e911_address_1 = "";
            $e911_address_2 = "";
            $e911_city = "";
            $e911_state = "";
            $e911_zip = "";
            $e911_zip_4 = "";
            $e911_callername = "";
            $e911_validated = "False";
        }
        $e911_request_data = query_e911_alert($e911_did);
        if ($e911_request_data) {
            $e911_alert_email_enable = "true";
            $e911_alert_email = $e911_request_data;
        } else {
            $e911_alert_email_enable = "false";
        }
    }

    if (isset($_REQUEST["id"]) && !$e911_success_got_from_server) {
        $action = "update";
        $e911_uuid = check_str($_REQUEST["id"]);
    }
    else {
        $action = "add";
    }

    if (count($_POST) > 0 && strlen($_POST["persistformvar"]) == 0) {

    	$msg = '';
    	if ($action == "update") {
    		$e911_uuid = check_str($_POST["e911_uuid"]);
    	}

		//check for all required data
		if (isset($_REQUEST["update_from_server"]) && !$e911_success_got_from_server) {
			$msg = $text['message-info_not_found_on_server'] ."<br>\n";
		} else {
			if (strlen($e911_did) == 0) { $msg .= $text['message-required'].$text['label-e911_did']."<br>\n"; }
			if (strlen($e911_address_1) == 0) { $msg .= $text['message-required'].$text['label-e911_address_1']."<br>\n"; }
			if (strlen($e911_city) == 0) { $msg .= $text['message-required'].$text['label-e911_city']."<br>\n"; }
			if (strlen($e911_state) == 0) { $msg .= $text['message-required'].$text['label-e911_state']."<br>\n"; }
			if (strlen($e911_zip) == 0) { $msg .= $text['message-required'].$text['label-e911_zip']."<br>\n"; }
			if (strlen($e911_callername) == 0) { $msg .= $text['message-required'].$text['label-e911_callername']."<br>\n"; }
		}

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
            $e911_data = array(
                    'e911_did' => $e911_did,
                    'e911_address_1' => $e911_address_1,
                    'e911_address_2' => $e911_address_2,
                    'e911_city' => $e911_city,
                    'e911_state' => $e911_state,
                    'e911_zip' => $e911_zip,
                    'e911_zip_4' => $e911_zip_4,
                    'e911_callername' => $e911_callername,
                    'e911_alert_email' => $e911_alert_email,
                    'e911_alert_email_enable' => $e911_alert_email_enable,
                );

    		if ($action == "add" && permission_exists('e911_add')) {

				// TOO MUCH PAIN HERE...
				if (!$e911_success_got_from_server) {
					// Make api calls here; Seems never would be used
					if (validate_e911_data($e911_data)) {
						if (add_e911_data($e911_data)) {
							if ($e911_alert_email_enable == 'True') {
								if (!add_e911_alert($e911_alert_email)) {
									$e911_alert_email_enable = "False";
								}
							}
						} else {
							$e911_validated = "Not added";
							$e911_alert_email_enable = "False";
						}
					} else {
						$e911_validated = "Not validated";
						$e911_alert_email_enable = "False";
					}
				}

    			//prepare the uuids
    			$e911_uuid = uuid();
    			//add the e911 info
    			$sql = "insert into v_e911 ";
    			$sql .= "(";
    			$sql .= "domain_uuid, ";
    			$sql .= "e911_uuid, ";
    			$sql .= "e911_did, ";
    			$sql .= "e911_address_1, ";
    			$sql .= "e911_address_2, ";
    			$sql .= "e911_city, ";
    			$sql .= "e911_state, ";
    			$sql .= "e911_zip, ";
    			$sql .= "e911_zip_4, ";
    			$sql .= "e911_callername, ";
    			$sql .= "e911_alert_email_enable, ";
    			$sql .= "e911_alert_email, ";
	                $sql .= "e911_validated";
    			$sql .= ") ";
    			$sql .= "values ";
    			$sql .= "(";
    			$sql .= "'$domain_uuid', ";
    			$sql .= "'".$e911_uuid."', ";
    			$sql .= "'$e911_did', ";
    			$sql .= "'$e911_address_1', ";
    			$sql .= "'$e911_address_2', ";
    			$sql .= "'$e911_city', ";
    			$sql .= "'$e911_state', ";
    			$sql .= "'$e911_zip', ";
    			$sql .= "'$e911_zip_4', ";
    			$sql .= "'$e911_callername', ";
    			$sql .= "'$e911_alert_email_enable', ";
    			$sql .= "'$e911_alert_email', ";
        	        $sql .= "'$e911_validated'";
    			$sql .= ")";
    			$db->exec(check_sql($sql));
                $sql_add = "ADD :".$sql;
    			unset($sql);
    		} //if ($action == "add")

    		if ($action == "update" && permission_exists('e911_edit')) {

				$e911_update_on_server = False;
				$e911_add_on_server = True;

				// Make api calls here
				$e911_server_data = query_e911_data($e911_did);

				if ($e911_server_data) {
					$e911_add_on_server = False;
					if (
						$e911_address_1 == $e911_server_data['e911_address_1'] &&
						$e911_address_2 == $e911_server_data['e911_address_2'] &&
						$e911_city == $e911_server_data['e911_city'] &&
						$e911_state == $e911_server_data['e911_state'] &&
						$e911_zip == $e911_server_data['e911_zip'] &&
						$e911_zip_4 == $e911_server_data['e911_zip_4'] &&
						$e911_callername == $e911_server_data['e911_callername']
					) {
						$e911_update_on_server = False;
					}
				}

				if ($e911_add_on_server) {
					if (validate_e911_data($e911_data)) {
						if (add_e911_data($e911_data)) {
							if ($e911_alert_email_enable == 'True') {
								if (!add_e911_alert($e911_alert_email)) {
									$e911_alert_email_enable = "False";
								}
							}
						} else {
							$e911_validated = "Not added";
							$e911_alert_email_enable = "False";
						}
					} else {
						$e911_validated = "Not validated";
						$e911_alert_email_enable = "False";
					}
				}

                if ($e911_update_on_server) {
                    $e911_update_result = update_e911($e911_data);
                    $e911_alert_email_enable = $e911_update_result['e911_alert_email_enable'];
                    $e911_validated = $e911_update_result['e911_validated'];
                } // No need to call update API if it was update from server.
    			// update e911 info
    			$sql = "update v_e911 set ";
    			$sql .= "e911_did = '$e911_did', ";
    			$sql .= "e911_address_1 = '$e911_address_1', ";
    			$sql .= "e911_address_2 = '$e911_address_2', ";
    			$sql .= "e911_city = '$e911_city', ";
    			$sql .= "e911_state = '$e911_state', ";
    			$sql .= "e911_zip = '$e911_zip', ";
    			$sql .= "e911_zip_4 = '$e911_zip_4', ";
    			$sql .= "e911_callername = '$e911_callername', ";
                $sql .= "e911_alert_email_enable = '$e911_alert_email_enable', ";
    			$sql .= "e911_validated = '$e911_validated', ";
    			$sql .= "e911_alert_email = '$e911_alert_email' ";
    			$sql .= "where domain_uuid = '$domain_uuid' ";
    			$sql .= "and e911_uuid = '$e911_uuid'";
    			$db->exec(check_sql($sql));
                $sql_update = "UPDATE :".$sql;
    			unset($sql);
    		} //if ($action == "update")

    	} //if ($_POST["persistformvar"] != "true")
    } // if (count($_POST) > 0 && strlen($_POST["persistformvar"]) == 0)

//get the destinations
	$sql = "select * from v_destinations ";
	$sql .= "where domain_uuid = '".check_str($domain_uuid)."' ";
	$sql .= "and destination_type = 'inbound' ";
	$sql .= "order by destination_number asc ";
	$prep_statement = $db->prepare(check_sql($sql));
	$prep_statement->execute();
	$destinations = $prep_statement->fetchAll(PDO::FETCH_ASSOC);
	unset ($sql, $prep_statement);

//pre-populate the form
	if (count($_GET) > 0 && $_POST["persistformvar"] != "true") {
		$e911_uuid = check_str($_GET["id"]);
		$sql = "select * from v_e911 ";
		$sql .= "where domain_uuid = '$domain_uuid' ";
		$sql .= "and e911_uuid = '$e911_uuid' ";
		$prep_statement = $db->prepare(check_sql($sql));
		$prep_statement->execute();
		$result = $prep_statement->fetchAll();
		foreach ($result as &$row) {
            //set the php variables
            $e911_did = $row["e911_did"];
            $e911_address_1 = $row["e911_address_1"];
            $e911_address_2 = $row["e911_address_2"];
            $e911_city = $row["e911_city"];
            $e911_state = $row["e911_state"];
            $e911_zip = $row["e911_zip"];
            $e911_zip_4 = $row["e911_zip_4"];
            $e911_callername = $row["e911_callername"];
            $e911_alert_email_enable = $row["e911_alert_email_enable"];
            $e911_alert_email = $row["e911_alert_email"];
            $e911_validated = $row["e911_validated"];
		}
		unset ($prep_statement);
	}

//show the header
	require_once "resources/header.php";
	if ($action == "update") {
		$document['title'] = $text['title-e911-edit'];
	}
	if ($action == "add") {
		$document['title'] = $text['title-e911-add'];
	}

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

//show the content
	echo "<form method='post' name='frm' action=''>\n";
	echo "<table width='100%' border='0' cellpadding='0' cellspacing='0'>\n";
	echo "<tr>\n";
	echo "<td align='left' width='30%' nowrap='nowrap'><b>";
	if ($action == "update") {
		echo $text['header-e911-edit'];
	}
	if ($action == "add") {
		echo $text['header-e911-add'];
	}
	echo "</b></td>\n";
	echo "<td width='70%' align='right'>";
	echo "	<input type='button' class='btn' name='' alt='".$text['button-back']."' onclick=\"window.location='e911.php'\" value='".$text['button-back']."'>";
    //echo "  <input type='submit' class='btn' name='update_from_server' alt='".$text['button-update_911']."' onclick=\"window.location='e911_update_from_server.php'\" value='".$text['button-update_911']."'>";
    echo "  <input type='submit' name='update_from_server' class='btn' value='".$text['button-update_911']."'>\n";
	echo "	<input type='submit' name='submit' class='btn' value='".$text['button-save']."'>\n";
	echo "</td>\n";
	echo "</tr>\n";

/*
    // DID
	echo "<tr>\n";
	echo "<td class='vncellreq' valign='top' align='left' nowrap='nowrap'>\n";
	echo "	".$text['label-e911_did']."\n";
	echo "</td>\n";
	echo "<td class='vtable' align='left'>\n";
	echo "	<input class='formfld' type='text' name='e911_did' maxlength='255' value=\"$e911_did\">\n";
	echo "<br />\n";
	echo $text['description-e911_did']."\n";
	echo "</td>\n";
	echo "</tr>\n";

*/

    echo "<tr>\n";
    echo "<td class='vncellreq' valign='top' align='left' nowrap='nowrap'>\n";
    echo "    ".$text['label-e911_did']."\n";
    echo "</td>\n";
    echo "<td class='vtable' align='left'>\n";
    if (count($destinations) > 0) {
        echo "  <select name='e911_did' id='e911_did' class='formfld'>\n";
        echo "  <option value=''></option>\n";
        foreach ($destinations as $row) {
			$row = array_map('escape', $row);

            $tmp = $row["destination_caller_id_number"];
            if(strlen($tmp) == 0){
                $tmp = $row["destination_number"];
            }
            if(strlen($tmp) > 0){
                if ($e911_did == $tmp) {
                    echo "      <option value='".$tmp."' selected='selected'>".$tmp."</option>\n";
                }
                else {
                    echo "      <option value='".$tmp."'>".$tmp."</option>\n";
                }
            }
        }
        echo "      </select>\n";
        echo "<br />\n";
        echo $text['description-e911_did']."\n";
    }
    else {
        echo "  <input type=\"button\" class=\"btn\" name=\"\" alt=\"".$text['button-add']."\" onclick=\"window.location='".PROJECT_PATH."/app/destinations/destinations.php'\" value='".$text['button-add']."'>\n";
    }
    unset ($prep_statement);
    echo "</td>\n";
    echo "</tr>\n";

    // Address 1
	echo "<tr>\n";
	echo "<td class='vncellreq' valign='top' align='left' nowrap='nowrap'>\n";
	echo "	".$text['label-e911_address_1']."\n";
	echo "</td>\n";
	echo "<td class='vtable' align='left'>\n";
	echo "	<input class='formfld' type='text' name='e911_address_1' maxlength='255' value=\"" . escape($e911_address_1) . "\">\n";
	echo "<br />\n";
	echo $text['description-e911_address_1']."\n";
	echo "</td>\n";
	echo "</tr>\n";

    // Address 2
    echo "<tr>\n";
    echo "<td class='vncellreq' valign='top' align='left' nowrap='nowrap'>\n";
    echo "  ".$text['label-e911_address_2']."\n";
    echo "</td>\n";
    echo "<td class='vtable' align='left'>\n";
    echo "  <input class='formfld' type='text' name='e911_address_2' maxlength='255' value=\"" . escape($e911_address_2) . "\">\n";
    echo "<br />\n";
    echo $text['description-e911_address_2']."\n";
    echo "</td>\n";
    echo "</tr>\n";

    // City
	echo "<tr>\n";
	echo "<td class='vncellreq' valign='top' align='left' nowrap='nowrap'>\n";
	echo "	".$text['label-e911_city']."\n";
	echo "</td>\n";
	echo "<td class='vtable' align='left'>\n";
	echo "	<input class='formfld' type='text' name='e911_city' maxlength='255' value=\"" . escape($e911_city) . "\">\n";
	echo "<br />\n";
	echo $text['description-e911_city']."\n";
	echo "</td>\n";
	echo "</tr>\n";

    // State
    echo "<tr>\n";
    echo "<td class='vncellreq' valign='top' align='left' nowrap='nowrap'>\n";
    echo "  ".$text['label-e911_state']."\n";
    echo "</td>\n";
    echo "<td class='vtable' align='left'>\n";
    echo "  <input class='formfld' type='text' name='e911_state' maxlength='255' value=\"" . escape($e911_state) . "\">\n";
    echo "<br />\n";
    echo $text['description-e911_state']."\n";
    echo "</td>\n";
    echo "</tr>\n";

    // Zip
    echo "<tr>\n";
    echo "<td class='vncellreq' valign='top' align='left' nowrap='nowrap'>\n";
    echo "  ".$text['label-e911_zip']."\n";
    echo "</td>\n";
    echo "<td class='vtable' align='left'>\n";
    echo "  <input class='formfld' type='text' name='e911_zip' maxlength='255' value=\"" . escape($e911_zip) . "\">\n";
    echo "<br />\n";
    echo $text['description-e911_zip']."\n";
    echo "</td>\n";
    echo "</tr>\n";


    // Zip+4
    echo "<tr>\n";
    echo "<td class='vncellreq' valign='top' align='left' nowrap='nowrap'>\n";
    echo "  ".$text['label-e911_zip_4']."\n";
    echo "</td>\n";
    echo "<td class='vtable' align='left'>\n";
    echo "  <input class='formfld' type='text' name='e911_zip_4' maxlength='255' value=\"" . escape($e911_zip_4) . "\">\n";
    echo "<br />\n";
    echo $text['description-e911_zip_4']."\n";
    echo "</td>\n";
    echo "</tr>\n";

    // Callername
    echo "<tr>\n";
    echo "<td class='vncellreq' valign='top' align='left' nowrap='nowrap'>\n";
    echo "  ".$text['label-e911_callername']."\n";
    echo "</td>\n";
    echo "<td class='vtable' align='left'>\n";
	echo "  <input class='formfld' type='text' name='e911_callername' maxlength='255' value=\"" . escape($e911_callername) . "\">\n";
    echo "<br />\n";
    echo $text['description-e911_callername']."\n";
    echo "</td>\n";
    echo "</tr>\n";

    // Alert email
    echo "<tr>\n";
    echo "<td class='vncellreq' valign='top' align='left' nowrap='nowrap'>\n";
    echo "  ".$text['label-e911_alert_email']."\n";
    echo "</td>\n";
    echo "<td class='vtable' align='left'>\n";
    echo "  <input class='formfld' type='text' name='e911_alert_email' maxlength='255' value=\"" . escape($e911_alert_email) . "\">\n";
    echo "<br />\n";
    echo $text['description-e911_alert_email']."\n";
    echo "</td>\n";
    echo "</tr>\n";

    // Alert email enable
    echo "<tr>\n";
    echo "<td class='vncellreq' valign='top' align='left' nowrap='nowrap'>\n";
    echo "    ".$text['label-e911_alert_email_enable']."\n";
    echo "</td>\n";
    echo "<td class='vtable' align='left'>\n";
    echo "    <select class='formfld' name='e911_alert_email_enable'>\n";
    if ($e911_alert_email_enable == "true") {
        echo "    <option value='true' selected='selected'>".$text['label-true']."</option>\n";
    }
    else {
        echo "    <option value='true'>".$text['label-true']."</option>\n";
    }
    if ($e911_alert_email_enable == "false") {
        echo "    <option value='false' selected >".$text['label-false']."</option>\n";
    }
    else {
        echo "    <option value='false'>".$text['label-false']."</option>\n";
    }
    echo "    </select>\n";
    echo "<br />\n";
    echo $text['description-e911_alert_email_enable']."\n";
    echo "</td>\n";
    echo "</tr>\n";

    // Validated
    echo "<tr>\n";
    echo "<td>\n";
    echo "<center><b>";
    if ($e911_validated == "") {
        echo "No information\n";
    } else {
        echo "$e911_validated\n";
    }
    echo "</b></center>";
    echo "<br />\n";
    echo "<br />\n";
//    echo $sql_update;
    echo "</td>\n";
    echo "</tr>\n";

	echo "	<tr>\n";
	echo "		<td colspan='2' align='right'>\n";
	if ($action == "update") {
		echo "		<input type='hidden' name='e911_uuid' value='" . escape($e911_uuid) . "'>\n";
        echo "      <input type='hidden' name='e911_validated' value='" . escape($e911_validated) . "'>\n";
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
