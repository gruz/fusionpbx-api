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
    Portions created by the Initial Developer are Copyright (C) 2013-2017
    the Initial Developer. All Rights Reserved.

    Contributor(s):
    Mark J Crane <markjcrane@fusionpbx.com>
    Luis Daniel Lucio Quiroz <dlucio@okay.com.mx>
*/

//includes

    require_once "root.php";
    require_once "resources/require.php";
    require_once "resources/check_auth.php";

//check permissions
    if (permission_exists('destinations_ext_add') || permission_exists('destinations_ext_edit')) {
        //access granted
    }
    else {
        echo "access denied";
        exit;
    }

//add multi-lingual support
    $language = new text;
    $text = $language->get();

//action add or update
    if (isset($_REQUEST["id"])) {
        $action = "update";
        $destination_ext_uuid = check_str($_REQUEST["id"]);
    }
    else {
        $action = "add";
        $prev_generated_destination_ext_uuid = uuid();
    }

    //get http post variables and set them to php variables

    //get total destination count from the database, check limit, if defined. Here we check against normal destinations
	if (!permission_exists('destination_domain')) {
		if ($action == 'add') {
			if ($_SESSION['limit']['destinations']['numeric'] != '') {
				$sql = "SELECT count(*) AS num_rows FROM v_destinations WHERE domain_uuid = '".$domain_uuid."' ";
				$prep_statement = $db->prepare($sql);
				if ($prep_statement) {
					$prep_statement->execute();
					$row = $prep_statement->fetch(PDO::FETCH_ASSOC);
					$total_destinations = $row['num_rows'];
				}
				unset($prep_statement, $row);
				if ($total_destinations >= $_SESSION['limit']['destinations']['numeric']) {
					messages::add($text['message-maximum_destinations'].' '.$_SESSION['limit']['destinations']['numeric'], 'negative');
					header('Location: destinations_ext.php');
					return;
				}
			}
		}
	}

    // Check for silence detection features
    $destination_ext_silence_detect_enabled = isset($_SESSION['silence_detect']['enabled']['boolean']) ? filter_var($_SESSION['silence_detect']['enabled']['boolean'], FILTER_VALIDATE_BOOLEAN) : False;
    if ($destination_ext_silence_detect_enabled) {
        $destination_ext_silence_detect_algo = isset($_SESSION['silence_detect']['algorithm']['text']) ? $_SESSION['silence_detect']['algorithm']['text'] : "";
    }

    if (count($_POST) > 0) {
        //set the variables
        $domain_uuid = trim($_POST["domain_uuid"]);
        $destination_ext_uuid = trim($_POST["destination_ext_uuid"]);
        $destination_ext_dialplan_main_uuid = trim($_POST["destination_ext_dialplan_main_uuid"]);
        $destination_ext_dialplan_main_details = array_values($_POST["destination_ext_dialplan_main_details"]);
        $destination_ext_dialplan_extensions_uuid = trim($_POST["destination_ext_dialplan_extensions_uuid"]);
        $destination_ext_dialplan_extensions_details = array_values($_POST["destination_ext_dialplan_extensions_details"]);
        $destination_ext_dialplan_invalid_details = array_values($_POST["destination_ext_dialplan_invalid_details"]);
        $destination_ext_number = trim($_POST["destination_ext_number"]);
        $db_destination_ext_number = trim($_POST["db_destination_ext_number"]);
        $destination_ext_variable = trim($_POST["destination_ext_variable"]);
        $destination_ext_variable_no_extension = trim($_POST["destination_ext_variable_no_extension"]);
        $destination_ext_silence_detect = trim($_POST["destination_ext_silence_detect"]);
        $destination_ext_callerid_name_prepend = trim($_POST["destination_ext_callerid_name_prepend"]);
        $destination_ext_callerid_number_prepend = trim($_POST["destination_ext_callerid_number_prepend"]);
        $destination_ext_enabled = trim($_POST["destination_ext_enabled"]);
        $destination_ext_description = trim($_POST["destination_ext_description"]);
        
        //convert the number to a regular expression
        $destination_ext_number_regex = string_to_regex($destination_ext_number);

        if (!$destination_ext_silence_detect_enabled) {
            $destination_ext_silence_detect = 'false';
        }

        $destination_ext_variable_no_extension = strlen($destination_ext_variable_no_extension) > 0 ? $destination_ext_variable_no_extension : False;
    }
    unset($_POST["db_destination_ext_number"]);

    // Set invalid name extension suffix from uuid of destination
    $invalid_name_id = (strlen($destination_ext_uuid) == 0)?explode("-", $prev_generated_destination_ext_uuid)[0]:explode("-", $destination_ext_uuid)[0];

    //process the http post. Here we process UPDATE request and putting/updating info in database
    if (count($_POST) > 0 && strlen($_POST["persistformvar"]) == 0) {

        $destination_ext_dialplan_main_details_data = reset($destination_ext_dialplan_main_details)['dialplan_detail_data'];
        $destination_ext_dialplan_extensions_details_data = reset($destination_ext_dialplan_extensions_details)['dialplan_detail_data'];
        $destination_ext_dialplan_invalid_details_data = reset($destination_ext_dialplan_invalid_details)['dialplan_detail_data'];
        $msg = '';
        if (strlen($destination_ext_number) == 0) { $msg .= $text['message-required']." ".$text['label-destination_ext_number']."<br>\n"; }
        if (strlen($destination_ext_dialplan_main_details_data) == 0) { $msg .= $text['message-required']." ".$text['label-detail_action_main']."<br>\n"; }
        if (strlen($destination_ext_dialplan_extensions_details_data) == 0) { $msg .= $text['message-required']." ".$text['label-detail_action_ext']."<br>\n"; }
        if (strlen($destination_ext_dialplan_invalid_details_data) == 0) { $msg .= $text['message-required']." ".$text['label-detail_action_invalid']."<br>\n"; }

        //check for duplicates
        if ($destination_ext_number != $db_destination_ext_number) {
            $sql = "SELECT ";
            $sql .= "(SELECT count(*) AS num_rows FROM v_destinations ";
            $sql .= "WHERE destination_number = '".$destination_ext_number."' ) + ";
            $sql .= "(SELECT count(*) FROM v_destinations_ext ";
            $sql .= "WHERE destination_ext_number = '".$destination_ext_number."') ";
            $sql .= "AS num_rows";
            $prep_statement = $db->prepare($sql);
            if ($prep_statement) {
                $prep_statement->execute();
                $row = $prep_statement->fetch(PDO::FETCH_ASSOC);
                if ($row['num_rows'] > 0) {
                    $msg .= $text['message-duplicate']."<br>\n";
                }
                unset($prep_statement);
            }
        }

        //show the message
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

            $destination_ext_domain = $_SESSION['domains'][$domain_uuid]['domain_name'];

            if (strlen($destination_ext_dialplan_invalid_details[0]['dialplan_detail_data']) > 0) {
                $add_dialplan_invalid = true;
            }

            //add or update the main dialplan part if the destination number is set

            // Adding invalid handler dialplan part
            if ($add_dialplan_invalid) {

                $dialplan_details = $destination_ext_dialplan_invalid_details;

                //remove the array from the HTTP POST
                unset($_POST["destination_ext_dialplan_invalid_details"]);

                //check to see if the dialplan exists
                $sql = "SELECT dialplan_uuid, dialplan_description FROM v_dialplans ";
                $sql .= "WHERE dialplan_name = '_invalid_ext_handler_".$invalid_name_id."' ";
                $sql .= "AND domain_uuid = '".$domain_uuid."' ";
                $prep_statement = $db->prepare($sql);
                if ($prep_statement) {
                    $prep_statement->execute();
                    $row = $prep_statement->fetch(PDO::FETCH_ASSOC);
                    if (strlen($row['dialplan_uuid']) > 0) {
                        $dialplan_uuid = $row['dialplan_uuid'];
                        $dialplan_description = $row['dialplan_description'];
                    } else {
                        $dialplan_uuid = uuid();
                    }
                    unset($prep_statement);
                } else {
                    $dialplan_uuid = uuid();
                }

                //build the dialplan array
                $dialplan["app_uuid"] = "cd838240-a1a6-4808-81c6-74ade7cfe100";
                $dialplan["dialplan_uuid"] = $dialplan_uuid;
                $dialplan["domain_uuid"] = $domain_uuid;
                $dialplan["dialplan_name"] = "_invalid_ext_handler_".$invalid_name_id;
                $dialplan["dialplan_number"] = '[invalid_ext]';
                $dialplan["dialplan_context"] = $destination_ext_domain;
                $dialplan["dialplan_continue"] = "true";
                $dialplan["dialplan_order"] = "889";
                $dialplan["dialplan_enabled"] = $destination_ext_enabled;
                $dialplan["dialplan_description"] = ($dialplan_description != '') ? $dialplan_description : "Invalid extension handler";

                $dialplan_detail_order = 10;
                $y = 0;

                // build XML dialplan
                if ($_SESSION['destinations']['dialplan_details']['boolean'] == "false") {
                    $dialplan["dialplan_xml"] = "<extension name=\"" . $dialplan["dialplan_name"] . "\" continue=\"false\" uuid=\"" . $dialplan["dialplan_uuid"] . "\">\n";
                    $dialplan["dialplan_xml"] .= "	<condition field=\"\${user_exists}\" expression=\"false\"/>\n";
                    $dialplan["dialplan_xml"] .= "	<condition field=\"\${call_direction}\" expression=\"inbound\"/>\n";
                    $dialplan["dialplan_xml"] .= "	<condition field=\"\${invalid_ext_id}\" expression=\"^" . $invalid_name_id . "\$\">\n";

                    if (strlen($destination_ext_variable) > 0 && strlen($destination_ext_variable_no_extension) > 0) {
                        $dialplan["dialplan_xml"] .= "		<action application=\"set\" data=\"" . $destination_ext_variable . "=" . $destination_ext_variable_no_extension . "\"/>\n";
                    }

                    $actions = explode(":", $dialplan_details[0]["dialplan_detail_data"]);
                    $dialplan_detail_type = array_shift($actions);
                    $dialplan_detail_data = join(':', $actions);
                    $dialplan["dialplan_xml"] .= "		<action application=\"".$dialplan_detail_type."\" data=\"".$dialplan_detail_data."\"/>\n";

                    $dialplan["dialplan_xml"] .= "	</condition>\n";
                    $dialplan["dialplan_xml"] .= "</extension>\n";
                }

                if ($_SESSION['destinations']['dialplan_details']['boolean'] == "true") {

                    $dialplan["dialplan_details"][$y]["domain_uuid"] = $domain_uuid;
                    $dialplan["dialplan_details"][$y]["dialplan_detail_tag"] = "condition";
                    $dialplan["dialplan_details"][$y]["dialplan_detail_type"] = '${user_exists}';
                    $dialplan["dialplan_details"][$y]["dialplan_detail_data"] = "^false$";
                    $dialplan["dialplan_details"][$y]["dialplan_detail_order"] = $dialplan_detail_order;
                    $y += 1;
                    $dialplan_detail_order += 10;

                    $dialplan["dialplan_details"][$y]["domain_uuid"] = $domain_uuid;
                    $dialplan["dialplan_details"][$y]["dialplan_detail_tag"] = "condition";
                    $dialplan["dialplan_details"][$y]["dialplan_detail_type"] = '${call_direction}';
                    $dialplan["dialplan_details"][$y]["dialplan_detail_data"] = "^inbound$";
                    $dialplan["dialplan_details"][$y]["dialplan_detail_order"] = $dialplan_detail_order;
                    $y += 1;
                    $dialplan_detail_order += 10;

                    $dialplan["dialplan_details"][$y]["domain_uuid"] = $domain_uuid;
                    $dialplan["dialplan_details"][$y]["dialplan_detail_tag"] = "condition";
                    $dialplan["dialplan_details"][$y]["dialplan_detail_type"] = '${invalid_ext_id}';
                    $dialplan["dialplan_details"][$y]["dialplan_detail_data"] = "^".$invalid_name_id."$";
                    $dialplan["dialplan_details"][$y]["dialplan_detail_order"] = $dialplan_detail_order;
                    $y += 1;
                    $dialplan_detail_order += 10;

                    //add the actions

                    if (strlen($destination_ext_variable) > 0 && strlen($destination_ext_variable_no_extension) > 0) {
                        $dialplan["dialplan_details"][$y]["domain_uuid"] = $domain_uuid;
                        $dialplan["dialplan_details"][$y]["dialplan_detail_tag"] = "action";
                        $dialplan["dialplan_details"][$y]["dialplan_detail_type"] = "set";
                        $dialplan["dialplan_details"][$y]["dialplan_detail_data"] = $destination_ext_variable . "=" . $destination_ext_variable_no_extension;
                        $dialplan["dialplan_details"][$y]["dialplan_detail_order"] = $dialplan_detail_order;
                        $dialplan_detail_order += 10;
                        $y += 1;
                    }

                    $actions = explode(":", $dialplan_details[0]["dialplan_detail_data"]);
                    $dialplan_detail_type = array_shift($actions);
                    $dialplan_detail_data = join(':', $actions);

                    if ($dialplan_detail_type == 'transfer') {
                        $invalid_ext_transfer = explode(" ", $dialplan_detail_data)[0];
                    }

                    $dialplan["dialplan_details"][$y]["domain_uuid"] = $domain_uuid;
                    $dialplan["dialplan_details"][$y]["dialplan_detail_tag"] = "action";
                    $dialplan["dialplan_details"][$y]["dialplan_detail_type"] = $dialplan_detail_type;
                    $dialplan["dialplan_details"][$y]["dialplan_detail_data"] = $dialplan_detail_data;
                    $dialplan["dialplan_details"][$y]["dialplan_detail_order"] = $dialplan_detail_order;
                    $dialplan_detail_order += 10;
                    $y += 1;


                }
                //delete the previous details
                $sql = "DELETE FROM v_dialplan_details ";
                $sql .= "WHERE dialplan_uuid = '".$dialplan_uuid."' ";
                $sql .= "AND (domain_uuid = '".$domain_uuid."') ";
                $db->exec(check_sql($sql));
                unset($sql);


                // Prepare an array
                // Prepare an array
                $array['dialplans'][] = $dialplan;
                unset($dialplan);

                //add the dialplan permission
                $p = new permissions;
                $p->add("dialplan_add", 'temp');
                $p->add("dialplan_detail_add", 'temp');
                $p->add("dialplan_edit", 'temp');
                $p->add("dialplan_detail_edit", 'temp');

                //save the dialplan
                $database = new database;
                $database->app_name = 'destinations_ext';
                $database->app_uuid = 'cd838240-a1a6-4808-81c6-74ade7cfe100';
                $database->save($array);
                $dialplan_response = $database->message;

                //remove the temporary permission
                $p->delete("dialplan_add", 'temp');
                $p->delete("dialplan_detail_add", 'temp');
                $p->delete("dialplan_edit", 'temp');
                $p->delete("dialplan_detail_edit", 'temp');

                //update the dialplan xml
                $dialplans = new dialplan;
                $dialplans->source = "details";
                $dialplans->destination = "database";
                $dialplans->uuid = $dialplan_uuid;
                $dialplans->xml();

                //synchronize the xml config
                save_dialplan_xml();

                //clear the cache
                $cache = new cache;
                $cache->delete("dialplan:".$destination_ext_domain);
                
                unset($dialplan_response, $dialplan, $dialplan_uuid, $database, $dialplans, $array);

            }
        // End of invalid handler part -----------------

            //determine whether save the main dialplan
            if (strlen($destination_ext_dialplan_main_details[0]['dialplan_detail_data']) > 0) {
                $add_dialplan_main = true;
            }

            //add or update the main dialplan part if the destination number is set
            if ($add_dialplan_main) {

                //get the array
                $dialplan_details = $destination_ext_dialplan_main_details;
                $dialplan_uuid = $destination_ext_dialplan_main_uuid;

                //remove the array from the HTTP POST
                unset($_POST["destination_ext_dialplan_main_details"]);

                //check to see if the dialplan exists
                if (strlen($dialplan_uuid) > 0) {
                    $sql = "SELECT dialplan_uuid, dialplan_name, dialplan_description FROM v_dialplans ";
                    $sql .= "WHERE dialplan_uuid = '".$dialplan_uuid."' ";
                    if (!permission_exists('destination_domain')) {
                        $sql .= "AND domain_uuid = '".$domain_uuid."' ";
                    }
                    $prep_statement = $db->prepare($sql);
                    if ($prep_statement) {
                        $prep_statement->execute();
                        $row = $prep_statement->fetch(PDO::FETCH_ASSOC);
                        if (strlen($row['dialplan_uuid']) > 0) {
                            $dialplan_name = $row['dialplan_name'];
                            $dialplan_description = $row['dialplan_description'];
                        } else {
                            $dialplan_uuid = uuid();
                        }
                        unset($prep_statement);
                    } else {
                        $dialplan_uuid = uuid();
                    }
                } else {
                    $dialplan_uuid = uuid();
                }

                //build the dialplan array
                $dialplan["app_uuid"] = "c03b422e-13a8-bd1b-e42b-b6b9b4d27ce4";
                $dialplan["dialplan_uuid"] = $dialplan_uuid;
                $dialplan["domain_uuid"] = $domain_uuid;
                $dialplan["dialplan_name"] = ($dialplan_name != '') ? $dialplan_name : format_phone($destination_ext_number);
                $dialplan["dialplan_number"] = $destination_ext_number;
                $dialplan["dialplan_context"] = "public";
                $dialplan["dialplan_continue"] = "false";
                $dialplan["dialplan_order"] = "100";
                $dialplan["dialplan_enabled"] = $destination_ext_enabled;
                $dialplan["dialplan_description"] = ($dialplan_description != '') ? $dialplan_description : $destination_ext_number . " main";

                $dialplan_detail_order = 10;
                $y = 0;


                if ($_SESSION['destinations']['dialplan_details']['boolean'] == "false") {
                    $dialplan["dialplan_xml"] = "<extension name=\"" . $dialplan["dialplan_name"] . "\" continue=\"false\" uuid=\"" . $dialplan["dialplan_uuid"] . "\">\n";
                    $dialplan["dialplan_xml"] .= "	<condition field=\"".$dialplan_detail_type."\" expression=\"" . $destination_ext_number_regex . "\">\n";
                    $dialplan["dialplan_xml"] .= "		<action application=\"export\" data=\"call_direction=inbound\" inline=\"true\"/>\n";
                    $dialplan["dialplan_xml"] .= "		<action application=\"set\" data=\"domain_uuid=".$_SESSION['domain_uuid']."\" inline=\"true\"/>\n";
                    $dialplan["dialplan_xml"] .= "		<action application=\"set\" data=\"domain_name=".$_SESSION['domain_name']."\" inline=\"true\"/>\n";
                    $dialplan["dialplan_xml"] .= "		<action application=\"set\" data=\"hangup_after_bridge=true\" inline=\"true\"/>\n";
                    $dialplan["dialplan_xml"] .= "		<action application=\"set\" data=\"continue_on_fail=true\" inline=\"true\"/>\n";
                    $dialplan["dialplan_xml"] .= "		<action application=\"set\" data=\"accountcode=" . $destination_ext_domain . "\"/>\n";

                    if (strlen($destination_ext_callerid_name_prepend) > 0) {
                        $dialplan["dialplan_xml"] .= "		<action application=\"set\" data=\"effective_caller_id_name=" . $destination_ext_callerid_name_prepend . "\${caller_id_name}\"/>\n";
                        $dialplan["dialplan_xml"] .= "		<action application=\"set\" data=\"origination_caller_id_name=" . $destination_ext_callerid_name_prepend . "\${caller_id_name}\"/>\n";
                    }
                    if (strlen($destination_ext_callerid_number_prepend) > 0) {
                        $dialplan["dialplan_xml"] .= "		<action application=\"set\" data=\"effective_caller_id_number=" . $destination_ext_callerid_number_prepend . "\${caller_id_number}\"/>\n";
                        $dialplan["dialplan_xml"] .= "		<action application=\"set\" data=\"origination_caller_id_number=" . $destination_ext_callerid_number_prepend . "\${caller_id_number}\"/>\n";
                    }

                    if (strlen($destination_ext_variable) > 0 and (isset($invalid_ext_transfer) or $destination_ext_variable_no_extension)) {
                        $dialplan["dialplan_xml"] .= "		<action application=\"set\" data=\"" . ($destination_ext_variable_no_extension ? $destination_ext_variable_no_extension : $invalid_ext_transfer) . "=" . $invalid_ext_transfer . "\"/>\n";
                    }

                    if ($destination_ext_silence_detect == 'true') {
                        $dialplan["dialplan_xml"] .= "		<action application=\"lua\" data=\"app_custom.lua silence_detect " . $destination_ext_silence_detect_algo . "\"/>\n";
                    }

                    $actions = explode(":", $dialplan_details[0]["dialplan_detail_data"]);
                    $dialplan_detail_type = array_shift($actions);
                    $dialplan_detail_data = join(':', $actions);
                    $dialplan["dialplan_xml"] .= "		<action application=\"".$dialplan_detail_type."\" data=\"".$dialplan_detail_data."\"/>\n";

                    $dialplan["dialplan_xml"] .= "	</condition>\n";
                    $dialplan["dialplan_xml"] .= "</extension>\n";
                }

                if ($_SESSION['destinations']['dialplan_details']['boolean'] == "true") { 

                    //check the destination number
                    $dialplan["dialplan_details"][$y]["domain_uuid"] = $domain_uuid;
                    $dialplan["dialplan_details"][$y]["dialplan_detail_tag"] = "condition";
                    // -- TODO - ?
                    if (strlen($_SESSION['dialplan']['destination']['text']) > 0) {
                        $dialplan["dialplan_details"][$y]["dialplan_detail_type"] = $_SESSION['dialplan']['destination']['text'];
                    }
                    else {
                        $dialplan["dialplan_details"][$y]["dialplan_detail_type"] = "destination_number";
                    }
                    $dialplan["dialplan_details"][$y]["dialplan_detail_data"] = $destination_ext_number_regex;
                    $dialplan["dialplan_details"][$y]["dialplan_detail_order"] = $dialplan_detail_order;
                    $y =+ 1;

                    //increment the dialplan detail order
                    $dialplan_detail_order = $dialplan_detail_order + 10;

                    $dialplan["dialplan_details"][$y]["domain_uuid"] = $domain_uuid;
                    $dialplan["dialplan_details"][$y]["dialplan_detail_tag"] = "action";
                    $dialplan["dialplan_details"][$y]["dialplan_detail_type"] = "set";
                    $dialplan["dialplan_details"][$y]["dialplan_detail_data"] = "call_direction=inbound";
                    $dialplan["dialplan_details"][$y]["dialplan_detail_order"] = $dialplan_detail_order;
                    $y += 1;
                    $dialplan_detail_order += 10;

                    $dialplan["dialplan_details"][$y]["domain_uuid"] = $domain_uuid;
                    $dialplan["dialplan_details"][$y]["dialplan_detail_tag"] = "action";
                    $dialplan["dialplan_details"][$y]["dialplan_detail_type"] = "set";
                    $dialplan["dialplan_details"][$y]["dialplan_detail_data"] = "domain_uuid=".$_SESSION['domain_uuid'];
                    $dialplan["dialplan_details"][$y]["dialplan_detail_order"] = $dialplan_detail_order;
                    $y += 1;
                    $dialplan_detail_order += 10;

                    $dialplan["dialplan_details"][$y]["domain_uuid"] = $domain_uuid;
                    $dialplan["dialplan_details"][$y]["dialplan_detail_tag"] = "action";
                    $dialplan["dialplan_details"][$y]["dialplan_detail_type"] = "set";
                    $dialplan["dialplan_details"][$y]["dialplan_detail_data"] = "domain_name=".$_SESSION['domain_name'];
                    $dialplan["dialplan_details"][$y]["dialplan_detail_order"] = $dialplan_detail_order;
                    $y += 1;
                    $dialplan_detail_order += 10;

                    $dialplan["dialplan_details"][$y]["domain_uuid"] = $domain_uuid;
                    $dialplan["dialplan_details"][$y]["dialplan_detail_tag"] = "action";
                    $dialplan["dialplan_details"][$y]["dialplan_detail_type"] = "set";
                    $dialplan["dialplan_details"][$y]["dialplan_detail_data"] = "hangup_after_bridge=true";
                    $dialplan["dialplan_details"][$y]["dialplan_detail_order"] = $dialplan_detail_order;
                    $y += 1;
                    $dialplan_detail_order += 10;

                    $dialplan["dialplan_details"][$y]["domain_uuid"] = $domain_uuid;
                    $dialplan["dialplan_details"][$y]["dialplan_detail_tag"] = "action";
                    $dialplan["dialplan_details"][$y]["dialplan_detail_type"] = "set";
                    $dialplan["dialplan_details"][$y]["dialplan_detail_data"] = "continue_on_fail=true";
                    $dialplan["dialplan_details"][$y]["dialplan_detail_order"] = $dialplan_detail_order;
                    $y += 1;
                    $dialplan_detail_order += 10;

                    //set the call accountcode
                    $dialplan["dialplan_details"][$y]["domain_uuid"] = $domain_uuid;
                    $dialplan["dialplan_details"][$y]["dialplan_detail_tag"] = "action";
                    $dialplan["dialplan_details"][$y]["dialplan_detail_type"] = "set";
                    $dialplan["dialplan_details"][$y]["dialplan_detail_data"] = "accountcode=".$destination_ext_domain;
                    $dialplan["dialplan_details"][$y]["dialplan_detail_order"] = $dialplan_detail_order;
                    $y += 1;
                    $dialplan_detail_order += 10;

                    if (strlen($destination_ext_variable) > 0 and (isset($invalid_ext_transfer) or $destination_ext_variable_no_extension)) {
                        
                        $dialplan["dialplan_details"][$y]["domain_uuid"] = $domain_uuid;
                        $dialplan["dialplan_details"][$y]["dialplan_detail_tag"] = "action";
                        $dialplan["dialplan_details"][$y]["dialplan_detail_type"] = "set";
                        $dialplan["dialplan_details"][$y]["dialplan_detail_data"] = $destination_ext_variable . "=" . ($destination_ext_variable_no_extension ? $destination_ext_variable_no_extension : $invalid_ext_transfer);
                        $dialplan["dialplan_details"][$y]["dialplan_detail_order"] = $dialplan_detail_order;
                        $dialplan_detail_order += 10;
                        $y += 1;
                    }

                    if (strlen($destination_ext_callerid_name_prepend) > 0) { 
                        $dialplan["dialplan_details"][$y]["domain_uuid"] = $domain_uuid;
                        $dialplan["dialplan_details"][$y]["dialplan_detail_tag"] = "action";
                        $dialplan["dialplan_details"][$y]["dialplan_detail_type"] = "set";
                        $dialplan["dialplan_details"][$y]["dialplan_detail_data"] = "effective_caller_id_name=" . $destination_ext_callerid_name_prepend . "\${caller_id_name}";
                        $dialplan["dialplan_details"][$y]["dialplan_detail_order"] = $dialplan_detail_order;
                        $dialplan_detail_order += 10;
                        $y += 1;

                        $dialplan["dialplan_details"][$y]["domain_uuid"] = $domain_uuid;
                        $dialplan["dialplan_details"][$y]["dialplan_detail_tag"] = "action";
                        $dialplan["dialplan_details"][$y]["dialplan_detail_type"] = "set";
                        $dialplan["dialplan_details"][$y]["dialplan_detail_data"] = "origination_caller_id_name=" . $destination_ext_callerid_name_prepend . "\${caller_id_name}";
                        $dialplan["dialplan_details"][$y]["dialplan_detail_order"] = $dialplan_detail_order;
                        $dialplan_detail_order += 10;
                        $y += 1;
                    }

                    if (strlen($destination_ext_callerid_number_prepend) > 0) { 
                        $dialplan["dialplan_details"][$y]["domain_uuid"] = $domain_uuid;
                        $dialplan["dialplan_details"][$y]["dialplan_detail_tag"] = "action";
                        $dialplan["dialplan_details"][$y]["dialplan_detail_type"] = "set";
                        $dialplan["dialplan_details"][$y]["dialplan_detail_data"] = "effective_caller_id_number=" . $destination_ext_callerid_number_prepend . "\${caller_id_number}";
                        $dialplan["dialplan_details"][$y]["dialplan_detail_order"] = $dialplan_detail_order;
                        $dialplan_detail_order += 10;
                        $y += 1;

                        $dialplan["dialplan_details"][$y]["domain_uuid"] = $domain_uuid;
                        $dialplan["dialplan_details"][$y]["dialplan_detail_tag"] = "action";
                        $dialplan["dialplan_details"][$y]["dialplan_detail_type"] = "set";
                        $dialplan["dialplan_details"][$y]["dialplan_detail_data"] = "origination_caller_id_number=" . $destination_ext_callerid_number_prepend . "\${caller_id_number}";
                        $dialplan["dialplan_details"][$y]["dialplan_detail_order"] = $dialplan_detail_order;
                        $dialplan_detail_order += 10;
                        $y += 1;
                    }

                    if ($destination_ext_silence_detect == 'true') {
                        $dialplan["dialplan_details"][$y]["domain_uuid"] = $domain_uuid;
                        $dialplan["dialplan_details"][$y]["dialplan_detail_tag"] = "action";
                        $dialplan["dialplan_details"][$y]["dialplan_detail_type"] = "lua";
                        $dialplan["dialplan_details"][$y]["dialplan_detail_data"] = "app_custom.lua silence_detect " . $destination_ext_silence_detect_algo;
                        $dialplan["dialplan_details"][$y]["dialplan_detail_order"] = $dialplan_detail_order;
                        $y += 1;
                        $dialplan_detail_order += 10;
                    }

                    $dialplan["dialplan_details"][$y]["domain_uuid"] = $domain_uuid;
                    $dialplan["dialplan_details"][$y]["dialplan_detail_tag"] = "action";
                    $dialplan["dialplan_details"][$y]["dialplan_detail_type"] = "set";
                    $dialplan["dialplan_details"][$y]["dialplan_detail_data"] = "call_direction=inbound";
                    $dialplan["dialplan_details"][$y]["dialplan_detail_order"] = $dialplan_detail_order;

                    $y++;
                    $dialplan_detail_order += 10;

                    //add the actions
                    $actions = explode(":", $dialplan_details[0]["dialplan_detail_data"]);
                    $dialplan_detail_type = array_shift($actions);
                    $dialplan_detail_data = join(':', $actions);

                    $dialplan["dialplan_details"][$y]["domain_uuid"] = $domain_uuid;
                    $dialplan["dialplan_details"][$y]["dialplan_detail_tag"] = "action";
                    $dialplan["dialplan_details"][$y]["dialplan_detail_type"] = $dialplan_detail_type;
                    $dialplan["dialplan_details"][$y]["dialplan_detail_data"] = $dialplan_detail_data;
                    $dialplan["dialplan_details"][$y]["dialplan_detail_order"] = $dialplan_detail_order;
                    $y += 1;
                    $dialplan_detail_order += 10;

                }

                //delete the previous details
                $sql = "DELETE FROM v_dialplan_details ";
                $sql .= "WHERE dialplan_uuid = '$dialplan_uuid' ";
                $sql .= "AND (domain_uuid = '$domain_uuid' OR domain_uuid IS NULL)";
                $db->exec(check_sql($sql));
                unset($sql);


                // Prepare an array
                $array['dialplans'][] = $dialplan;
                unset($dialplan);

                //add the dialplan permission
                $p = new permissions;
                $p->add("dialplan_add", 'temp');
                $p->add("dialplan_detail_add", 'temp');
                $p->add("dialplan_edit", 'temp');
                $p->add("dialplan_detail_edit", 'temp');

                //save the dialplan
                $database = new database;
                $database->app_name = 'destinations_ext';
                $database->app_uuid = 'cd838240-a1a6-4808-81c6-74ade7cfe100';
                $database->save($array);
                $dialplan_response = $database->message;

                //remove the temporary permission
                $p->delete("dialplan_add", 'temp');
                $p->delete("dialplan_detail_add", 'temp');
                $p->delete("dialplan_edit", 'temp');
                $p->delete("dialplan_detail_edit", 'temp');

                //update the dialplan xml
                $dialplans = new dialplan;
                $dialplans->source = "details";
                $dialplans->destination = "database";
                $dialplans->uuid = $dialplan_uuid;
                $dialplans->xml();

                //synchronize the xml config
                save_dialplan_xml();

                //clear the cache
                $cache = new cache;
                $cache->delete("dialplan:public");
                $cache->delete("dialplan:public:".$destination_ext_number);

                $destination_ext_dialplan_main_uuid = $dialplan_uuid;
                
                unset($dialplan_response, $dialplan, $dialplan_uuid, $database, $dialplans, $array);


            } else {
                //add or update the dialplan if the destination number is set 
                //remove empty dialplan details from POST array so doesn't attempt to insert below
                unset($_POST["destination_ext_dialplan_main_details"]);
            }

        // End of main part -----------------------------

            if (strlen($dialplan_details[0]['dialplan_detail_data']) > 0) {
                $add_dialplan_extensions = true;
            }

            //add or update the main dialplan part if the destination number is set
            if ($add_dialplan_extensions) {

                $dialplan_details = $destination_ext_dialplan_extensions_details;
                $dialplan_uuid = $destination_ext_dialplan_extensions_uuid;

                //remove the array from the HTTP POST
                unset($_POST["destination_ext_dialplan_extensions_details"]);

                //check to see if the dialplan exists
                if (strlen($dialplan_uuid) > 0) {
                    $sql = "SELECT dialplan_uuid, dialplan_name, dialplan_description FROM v_dialplans ";
                    $sql .= "WHERE dialplan_uuid = '".$dialplan_uuid."' ";
                    if (!permission_exists('destination_domain')) {
                        $sql .= "AND domain_uuid = '".$domain_uuid."' ";
                    }
                    $prep_statement = $db->prepare($sql);
                    if ($prep_statement) {
                        $prep_statement->execute();
                        $row = $prep_statement->fetch(PDO::FETCH_ASSOC);
                        if (strlen($row['dialplan_uuid']) > 0) {
                            $dialplan_uuid = $row['dialplan_uuid'];
                            $dialplan_name = $row['dialplan_name'];
                            $dialplan_description = $row['dialplan_description'];
                        } else {
                            $dialplan_uuid = uuid();
                        }
                        unset($prep_statement);
                    } else {
                        $dialplan_uuid = uuid();
                    }
                } else {
                    $dialplan_uuid = uuid();
                }

                //build the dialplan array
                $dialplan["app_uuid"] = "c03b422e-13a8-bd1b-e42b-b6b9b4d27ce4";
                if (strlen($dialplan_uuid) > 0) {
                    $dialplan["dialplan_uuid"] = $dialplan_uuid;
                }
                $dialplan["domain_uuid"] = $domain_uuid;
                $dialplan["dialplan_name"] = ($dialplan_name != '') ? $dialplan_name : format_phone($destination_ext_number) . " + ext";
                $dialplan["dialplan_number"] = $destination_ext_number . "\d{1,5}";
                $dialplan["dialplan_context"] = "public";
                $dialplan["dialplan_continue"] = "false";
                $dialplan["dialplan_order"] = "100";
                $dialplan["dialplan_enabled"] = $destination_ext_enabled;
                $dialplan["dialplan_description"] = ($dialplan_description != '') ? $dialplan_description : $destination_ext_number . " extension";

                $dialplan_detail_order = 10;
                $y = 0;

                // Change regex to support extensions (add \d part)
                $destination_ext_number_regex = str_replace(")$", "(\d{1,5})$", $destination_ext_number_regex);
                $destination_ext_number_regex = str_replace("^(", "^", $destination_ext_number_regex);


                // build XML dialplan
                if ($_SESSION['destinations']['dialplan_details']['boolean'] == "false") {
                    $dialplan["dialplan_xml"] = "<extension name=\"" . $dialplan["dialplan_name"] . "\" continue=\"false\" uuid=\"" . $dialplan["dialplan_uuid"] . "\">\n";
                    $dialplan["dialplan_xml"] .= "	<condition field=\"".$dialplan_detail_type."\" expression=\"" . $destination_ext_number_regex . "\">\n";
                    $dialplan["dialplan_xml"] .= "		<action application=\"export\" data=\"call_direction=inbound\" inline=\"true\"/>\n";
                    $dialplan["dialplan_xml"] .= "		<action application=\"set\" data=\"domain_uuid=".$_SESSION['domain_uuid']."\" inline=\"true\"/>\n";
                    $dialplan["dialplan_xml"] .= "		<action application=\"set\" data=\"domain_name=".$_SESSION['domain_name']."\" inline=\"true\"/>\n";
                    $dialplan["dialplan_xml"] .= "		<action application=\"set\" data=\"hangup_after_bridge=true\" inline=\"true\"/>\n";
                    $dialplan["dialplan_xml"] .= "		<action application=\"set\" data=\"continue_on_fail=true\" inline=\"true\"/>\n";
                    $dialplan["dialplan_xml"] .= "		<action application=\"set\" data=\"accountcode=" . $destination_ext_domain . "\"/>\n";
                    $dialplan["dialplan_xml"] .= "		<action application=\"set\" data=\"invalid_ext_id=" . $invalid_name_id . "\"/>\n";

                    if (strlen($destination_ext_variable) > 0) {
                        $dialplan["dialplan_xml"] .= "		<action application=\"set\" data=\"" . $destination_ext_variable . "=$1\"/>\n";
                    }

                    if (strlen($destination_ext_callerid_name_prepend) > 0) {
                        $dialplan["dialplan_xml"] .= "		<action application=\"set\" data=\"effective_caller_id_name=" . $destination_ext_callerid_name_prepend . "\${caller_id_name}\"/>\n";
                    }
                    if (strlen($destination_ext_callerid_number_prepend) > 0) {
                        $dialplan["dialplan_xml"] .= "		<action application=\"set\" data=\"effective_caller_id_number=" . $destination_ext_callerid_number_prepend . "\${caller_id_number}\"/>\n";
                    }

                    if ($destination_ext_silence_detect == 'true') {
                        $dialplan["dialplan_xml"] .= "		<action application=\"lua\" data=\"app_custom.lua silence_detect " . $destination_ext_silence_detect_algo . "\"/>\n";
                    }

                    $actions = explode(":", $dialplan_details[0]["dialplan_detail_data"]);
                    $dialplan_detail_type = array_shift($actions);
                    $dialplan_detail_data = join(':', $actions);
                    $dialplan["dialplan_xml"] .= "		<action application=\"".$dialplan_detail_type."\" data=\"".$dialplan_detail_data."\"/>\n";

                    $dialplan["dialplan_xml"] .= "	</condition>\n";
                    $dialplan["dialplan_xml"] .= "</extension>\n";
                }

                //dialplan details

                if ($_SESSION['destinations']['dialplan_details']['boolean'] == "true") {

                    //check the destination number
                    $dialplan["dialplan_details"][$y]["domain_uuid"] = $domain_uuid;
                    $dialplan["dialplan_details"][$y]["dialplan_detail_tag"] = "condition";
                    // -- TODO - ?
                    if (strlen($_SESSION['dialplan']['destination']['text']) > 0) {
                        $dialplan["dialplan_details"][$y]["dialplan_detail_type"] = $_SESSION['dialplan']['destination']['text'];
                    }
                    else {
                        $dialplan["dialplan_details"][$y]["dialplan_detail_type"] = "destination_number";
                    }

                    $dialplan["dialplan_details"][$y]["dialplan_detail_data"] = $destination_ext_number_regex;
                    $dialplan["dialplan_details"][$y]["dialplan_detail_order"] = $dialplan_detail_order;
                    $y += 1;

                    //increment the dialplan detail order
                    $dialplan_detail_order += 10;

                    $dialplan["dialplan_details"][$y]["domain_uuid"] = $domain_uuid;
                    $dialplan["dialplan_details"][$y]["dialplan_detail_tag"] = "action";
                    $dialplan["dialplan_details"][$y]["dialplan_detail_type"] = "set";
                    $dialplan["dialplan_details"][$y]["dialplan_detail_data"] = "call_direction=inbound";
                    $dialplan["dialplan_details"][$y]["dialplan_detail_order"] = $dialplan_detail_order;
                    $y += 1;
                    $dialplan_detail_order += 10;

                    $dialplan["dialplan_details"][$y]["domain_uuid"] = $domain_uuid;
                    $dialplan["dialplan_details"][$y]["dialplan_detail_tag"] = "action";
                    $dialplan["dialplan_details"][$y]["dialplan_detail_type"] = "set";
                    $dialplan["dialplan_details"][$y]["dialplan_detail_data"] = "domain_uuid=".$_SESSION['domain_uuid'];
                    $dialplan["dialplan_details"][$y]["dialplan_detail_order"] = $dialplan_detail_order;
                    $y += 1;
                    $dialplan_detail_order += 10;

                    $dialplan["dialplan_details"][$y]["domain_uuid"] = $domain_uuid;
                    $dialplan["dialplan_details"][$y]["dialplan_detail_tag"] = "action";
                    $dialplan["dialplan_details"][$y]["dialplan_detail_type"] = "set";
                    $dialplan["dialplan_details"][$y]["dialplan_detail_data"] = "domain_name=".$_SESSION['domain_name'];
                    $dialplan["dialplan_details"][$y]["dialplan_detail_order"] = $dialplan_detail_order;
                    $y += 1;
                    $dialplan_detail_order += 10;

                    $dialplan["dialplan_details"][$y]["domain_uuid"] = $domain_uuid;
                    $dialplan["dialplan_details"][$y]["dialplan_detail_tag"] = "action";
                    $dialplan["dialplan_details"][$y]["dialplan_detail_type"] = "set";
                    $dialplan["dialplan_details"][$y]["dialplan_detail_data"] = "hangup_after_bridge=true";
                    $dialplan["dialplan_details"][$y]["dialplan_detail_order"] = $dialplan_detail_order;
                    $y += 1;
                    $dialplan_detail_order += 10;

                    $dialplan["dialplan_details"][$y]["domain_uuid"] = $domain_uuid;
                    $dialplan["dialplan_details"][$y]["dialplan_detail_tag"] = "action";
                    $dialplan["dialplan_details"][$y]["dialplan_detail_type"] = "set";
                    $dialplan["dialplan_details"][$y]["dialplan_detail_data"] = "continue_on_fail=true";
                    $dialplan["dialplan_details"][$y]["dialplan_detail_order"] = $dialplan_detail_order;
                    $y += 1;
                    $dialplan_detail_order += 10;

                    //set the call accountcode
                    $dialplan["dialplan_details"][$y]["domain_uuid"] = $domain_uuid;
                    $dialplan["dialplan_details"][$y]["dialplan_detail_tag"] = "action";
                    $dialplan["dialplan_details"][$y]["dialplan_detail_type"] = "set";
                    $dialplan["dialplan_details"][$y]["dialplan_detail_data"] = "accountcode=".$destination_ext_domain;
                    $dialplan["dialplan_details"][$y]["dialplan_detail_order"] = $dialplan_detail_order;
                    $y += 1;
                    $dialplan_detail_order += 10;

                    $dialplan["dialplan_details"][$y]["domain_uuid"] = $domain_uuid;
                    $dialplan["dialplan_details"][$y]["dialplan_detail_tag"] = "action";
                    $dialplan["dialplan_details"][$y]["dialplan_detail_type"] = "set";
                    $dialplan["dialplan_details"][$y]["dialplan_detail_data"] = "invalid_ext_id=".$invalid_name_id."";
                    $dialplan["dialplan_details"][$y]["dialplan_detail_order"] = $dialplan_detail_order;
                    $y += 1;
                    $dialplan_detail_order += 10;

                    if (strlen($destination_ext_variable) > 0) {
                        
                        $dialplan["dialplan_details"][$y]["domain_uuid"] = $domain_uuid;
                        $dialplan["dialplan_details"][$y]["dialplan_detail_tag"] = "action";
                        $dialplan["dialplan_details"][$y]["dialplan_detail_type"] = "set";
                        $dialplan["dialplan_details"][$y]["dialplan_detail_data"] = $destination_ext_variable."=$1";
                        $dialplan["dialplan_details"][$y]["dialplan_detail_order"] = $dialplan_detail_order;
                        $y += 1;
                        $dialplan_detail_order += 10;
                    }

                    if (strlen($destination_ext_callerid_name_prepend) > 0) { 
                        $dialplan["dialplan_details"][$y]["domain_uuid"] = $domain_uuid;
                        $dialplan["dialplan_details"][$y]["dialplan_detail_tag"] = "action";
                        $dialplan["dialplan_details"][$y]["dialplan_detail_type"] = "set";
                        $dialplan["dialplan_details"][$y]["dialplan_detail_data"] = "effective_caller_id_name=" . $destination_ext_callerid_name_prepend . "\${caller_id_name}";
                        $dialplan["dialplan_details"][$y]["dialplan_detail_order"] = $dialplan_detail_order;
                        $dialplan_detail_order += 10;
                        $y += 1;
                    }

                    if (strlen($destination_ext_callerid_number_prepend) > 0) { 
                        $dialplan["dialplan_details"][$y]["domain_uuid"] = $domain_uuid;
                        $dialplan["dialplan_details"][$y]["dialplan_detail_tag"] = "action";
                        $dialplan["dialplan_details"][$y]["dialplan_detail_type"] = "set";
                        $dialplan["dialplan_details"][$y]["dialplan_detail_data"] = "effective_caller_id_number=" . $destination_ext_callerid_number_prepend . "\${caller_id_number}";
                        $dialplan["dialplan_details"][$y]["dialplan_detail_order"] = $dialplan_detail_order;
                        $dialplan_detail_order += 10;
                        $y += 1;
                    }

                    if ($destination_ext_silence_detect == 'true') {
                        $dialplan["dialplan_details"][$y]["domain_uuid"] = $domain_uuid;
                        $dialplan["dialplan_details"][$y]["dialplan_detail_tag"] = "action";
                        $dialplan["dialplan_details"][$y]["dialplan_detail_type"] = "lua";
                        $dialplan["dialplan_details"][$y]["dialplan_detail_data"] = "app_custom.lua silence_detect " . $destination_ext_silence_detect_algo;
                        $dialplan["dialplan_details"][$y]["dialplan_detail_order"] = $dialplan_detail_order;
                        $y += 1;
                        $dialplan_detail_order += 10;
                    }

                    //add the actions

                    $actions = explode(":", $dialplan_details[0]["dialplan_detail_data"]);
                    $dialplan_detail_type = array_shift($actions);
                    $dialplan_detail_data = join(':', $actions);

                    $dialplan["dialplan_details"][$y]["domain_uuid"] = $domain_uuid;
                    $dialplan["dialplan_details"][$y]["dialplan_detail_tag"] = "action";
                    $dialplan["dialplan_details"][$y]["dialplan_detail_type"] = $dialplan_detail_type;
                    $dialplan["dialplan_details"][$y]["dialplan_detail_data"] = $dialplan_detail_data;
                    $dialplan["dialplan_details"][$y]["dialplan_detail_order"] = $dialplan_detail_order;
                    $y += 1;
                    $dialplan_detail_order += 10;

                }

                //delete the previous details
                $sql = "DELETE FROM v_dialplan_details ";
                $sql .= "WHERE dialplan_uuid = '".$dialplan_uuid."' ";
                $sql .= "AND (domain_uuid = '".$domain_uuid."' OR domain_uuid IS NULL)";
                $db->exec(check_sql($sql));
                unset($sql);

                // Prepare an array
                $array['dialplans'][] = $dialplan;
                unset($dialplan);

                //add the dialplan permission
                $p = new permissions;
                $p->add("dialplan_add", 'temp');
                $p->add("dialplan_detail_add", 'temp');
                $p->add("dialplan_edit", 'temp');
                $p->add("dialplan_detail_edit", 'temp');

                //save the dialplan
                $database = new database;
                $database->app_name = 'destinations_ext';
                $database->app_uuid = 'cd838240-a1a6-4808-81c6-74ade7cfe100';
                $database->save($array);
                $dialplan_response = $database->message;

                //remove the temporary permission
                $p->delete("dialplan_add", 'temp');
                $p->delete("dialplan_detail_add", 'temp');
                $p->delete("dialplan_edit", 'temp');
                $p->delete("dialplan_detail_edit", 'temp');

                //update the dialplan xml
                $dialplans = new dialplan;
                $dialplans->source = "details";
                $dialplans->destination = "database";
                $dialplans->uuid = $dialplan_uuid;
                $dialplans->xml();

                //synchronize the xml config
                save_dialplan_xml();

                //clear the cache
                $cache = new cache;
                $cache->delete("dialplan:public");
                $cache->delete("dialplan:public:".$destination_ext_number_regex);

                $destination_ext_dialplan_extensions_uuid = $dialplan_uuid;
                
                unset($dialplan_response, $dialplan, $dialplan_uuid, $database, $dialplans, $array);


            } else {
                //add or update the dialplan if the destination number is set 
                //remove empty dialplan details from POST array so doesn't attempt to insert below
                unset($_POST["destination_ext_dialplan_extensions_details"]);
            }


        // End of extensions part -----------------------
        // End of adding dialplans section

            if ($action == 'add') {
                $destination_ext_uuid = $prev_generated_destination_ext_uuid;
                $_POST['destination_ext_uuid'] = $destination_ext_uuid;
                $sql = "INSERT INTO v_destinations_ext (";
                $sql .= " domain_uuid,";
                $sql .= " destination_ext_uuid,";
                $sql .= " destination_ext_dialplan_main_uuid,";
                $sql .= " destination_ext_dialplan_extensions_uuid,";
                $sql .= " destination_ext_number,";
                $sql .= " destination_ext_variable,";
                $sql .= " destination_ext_variable_no_extension,";
                $sql .= " destination_ext_silence_detect,";
                $sql .= " destination_ext_callerid_name_prepend,";
                $sql .= " destination_ext_callerid_number_prepend,";
                $sql .= " destination_ext_enabled,";
                $sql .= " destination_ext_description";
                $sql .= ") VALUES (";
                $sql .= " '".$domain_uuid."',";
                $sql .= " '".$destination_ext_uuid."',";
                $sql .= " '".$destination_ext_dialplan_main_uuid."',";
                $sql .= " '".$destination_ext_dialplan_extensions_uuid."',";
                $sql .= " '".$destination_ext_number."',";
                $sql .= " '".$destination_ext_variable."',";
                $sql .= " '".$destination_ext_variable_no_extension."',";
                $sql .= " '".$destination_ext_silence_detect."',";
                $sql .= " '".$destination_ext_callerid_name_prepend."',";
                $sql .= " '".$destination_ext_callerid_number_prepend."',";
                $sql .= " '".$destination_ext_enabled."',";
                $sql .= " '".$destination_ext_description."')";

                $db->exec(check_sql($sql));
                unset($sql);
            } elseif ($action == 'update') {
                $_POST['destination_ext_uuid'] = $destination_ext_uuid;

                $sql = "UPDATE v_destinations_ext SET";
                $sql .= " destination_ext_dialplan_main_uuid = '".$destination_ext_dialplan_main_uuid."',";
                $sql .= " destination_ext_dialplan_extensions_uuid = '".$destination_ext_dialplan_extensions_uuid."',";
                $sql .= " destination_ext_number = '".$destination_ext_number."',";
                $sql .= " destination_ext_variable = '".$destination_ext_variable."',";
                $sql .= " destination_ext_variable_no_extension = '".$destination_ext_variable_no_extension."',";
                $sql .= " destination_ext_silence_detect = '".$destination_ext_silence_detect."',";
                $sql .= " destination_ext_callerid_name_prepend = '".$destination_ext_callerid_name_prepend."',";
                $sql .= " destination_ext_callerid_number_prepend = '".$destination_ext_callerid_number_prepend."',";
                $sql .= " destination_ext_enabled = '".$destination_ext_enabled."',";
                $sql .= " destination_ext_description = '".$destination_ext_description."'";
                $sql .= " WHERE destination_ext_uuid = '".$destination_ext_uuid."'";
                $sql .= " AND domain_uuid = '".$domain_uuid."'";

                $db->exec(check_sql($sql));
                unset($sql);

            } else {
                // Here should be some errors handler
                $all_vars = get_defined_vars();
                echo "Action is not add or update....<br>";
                echo "<pre>";
                var_dump($all_vars);
                echo "</pre>";
                exit(0);
            }
        //redirect the user
            if ($action == "add") {
                $_SESSION["message"] = $text['message-add'];
            }
            if ($action == "update") {
                $_SESSION["message"] = $text['message-update'];
            }
            header("Location: destination_ext_edit.php?id=".$destination_ext_uuid);
            return;
        } //if ($_POST["persistformvar"] != "true")
    } //(count($_POST) > 0 && strlen($_POST["persistformvar"]) == 0)

//initialize the destinations object
    $destination = new destinations;

//pre-populate the form. Here we add in the form data
    // Ok, we're added one more query to database....


    if (strlen($destination_ext_uuid) > 0) {
        $sql = "SELECT * FROM v_destinations_ext ";
        $sql .= "WHERE domain_uuid = '".$domain_uuid."' ";
        $sql .= "AND destination_ext_uuid = '".$destination_ext_uuid."' LIMIT 1";

        $prep_statement = $db->prepare(check_sql($sql));
        $prep_statement->execute();
        $result = $prep_statement->fetchAll(PDO::FETCH_NAMED);
        if (isset($result[0]["domain_uuid"])) {
            $domain_uuid = $result[0]["domain_uuid"];
            $destination_ext_uuid = $result[0]["destination_ext_uuid"];
            $destination_ext_dialplan_main_uuid = $result[0]["destination_ext_dialplan_main_uuid"];
            $destination_ext_dialplan_extensions_uuid = $result[0]["destination_ext_dialplan_extensions_uuid"];
            $destination_ext_number = $result[0]["destination_ext_number"];
            $destination_ext_variable = $result[0]["destination_ext_variable"];
            $destination_ext_variable_no_extension = $result[0]["destination_ext_variable_no_extension"];
            $destination_ext_silence_detect = $result[0]["destination_ext_silence_detect"];
            $destination_ext_callerid_name_prepend = $result[0]["destination_ext_callerid_name_prepend"];
            $destination_ext_callerid_number_prepend = $result[0]["destination_ext_callerid_number_prepend"];
            $destination_ext_enabled = $result[0]["destination_ext_enabled"];
            $destination_ext_description = $result[0]["destination_ext_description"];
        }
    }


    //get the main dialplan details in an array
    if (strlen($destination_ext_dialplan_main_uuid) > 0 or $action != "update") {
        $sql = "SELECT * FROM v_dialplan_details ";
        $sql .= "WHERE (domain_uuid = '".$domain_uuid."' OR domain_uuid is null) ";
        $sql .= "AND dialplan_uuid = '".$destination_ext_dialplan_main_uuid."' ";
        $sql .= "ORDER BY dialplan_detail_group ASC, dialplan_detail_order ASC";
        $prep_statement = $db->prepare(check_sql($sql));
        $prep_statement->execute();
        $destination_ext_dialplan_main_details = $prep_statement->fetchAll(PDO::FETCH_NAMED);
        unset ($prep_statement, $sql);

        if (count($destination_ext_dialplan_main_details) == 0) {
            $destination_ext_dialplan_main_details[0]['domain_uuid'] = $domain_uuid;
            $destination_ext_dialplan_main_details[0]['dialplan_uuid'] = $destination_ext_dialplan_main_uuid;
            $destination_ext_dialplan_main_details[0]['dialplan_detail_type'] = '';
            $destination_ext_dialplan_main_details[0]['dialplan_detail_data'] = '';
            $destination_ext_dialplan_main_details[0]['dialplan_detail_order'] = '';
        }
    }


    // Get extensions dialplan details into array
    if (strlen($destination_ext_dialplan_extensions_uuid) > 0 or $action != "update") {
        $sql = "SELECT * FROM v_dialplan_details ";
        $sql .= "WHERE (domain_uuid = '".$domain_uuid."' OR domain_uuid IS NULL) ";
        $sql .= "AND dialplan_uuid = '".$destination_ext_dialplan_extensions_uuid."' ";
        $sql .= "ORDER BY dialplan_detail_group ASC, dialplan_detail_order ASC";
        $prep_statement = $db->prepare(check_sql($sql));
        $prep_statement->execute();
        $destination_ext_dialplan_extensions_details = $prep_statement->fetchAll(PDO::FETCH_NAMED);
        unset ($prep_statement, $sql);

        if (count($destination_ext_dialplan_extensions_details) == 0) {
            $destination_ext_dialplan_extensions_details[0]['domain_uuid'] = $domain_uuid;
            $destination_ext_dialplan_extensions_details[0]['dialplan_uuid'] = $destination_ext_dialplan_extensions_uuid;
            $destination_ext_dialplan_extensions_details[0]['dialplan_detail_type'] = '';
            $destination_ext_dialplan_extensions_details[0]['dialplan_detail_data'] = '';
            $destination_ext_dialplan_extensions_details[0]['dialplan_detail_order'] = '';
        }
    }

    // Get invalid dialplan details into array
    $sql = "SELECT * FROM v_dialplan_details ";
    $sql .= "WHERE domain_uuid = '".$domain_uuid."' ";
    $sql .= "AND dialplan_uuid = (";
    $sql .= "SELECT dialplan_uuid FROM v_dialplans ";
    $sql .= "WHERE domain_uuid = '".$domain_uuid."' ";
    $sql .= "AND dialplan_name = '_invalid_ext_handler_".$invalid_name_id."'";
    $sql .= " LIMIT 1) ";
    $sql .= "ORDER BY dialplan_detail_group ASC, dialplan_detail_order ASC";
    $prep_statement = $db->prepare(check_sql($sql));
    $prep_statement->execute();
    $destination_ext_dialplan_invalid_details = $prep_statement->fetchAll(PDO::FETCH_NAMED);
    unset ($prep_statement, $sql);

    if (count($destination_ext_dialplan_invalid_details) == 0) {
        $destination_ext_dialplan_invalid_details[0]['domain_uuid'] = $domain_uuid;
        $destination_ext_dialplan_invalid_details[0]['dialplan_uuid'] = uuid();
        $destination_ext_dialplan_invalid_details[0]['dialplan_detail_type'] = '';
        $destination_ext_dialplan_invalid_details[0]['dialplan_detail_data'] = '';
        $destination_ext_dialplan_invalid_details[0]['dialplan_detail_order'] = '';
    }


//show the header
    require_once "resources/header.php";
    if ($action == "update") {
        $document['title'] = $text['title-destination_ext-edit'];
    }
    else if ($action == "add") {
        $document['title'] = $text['title-destination_ext-add'];
    }

    //show the content
    echo "<form method='post' name='frm' action=''>\n";
    echo "<table width='100%' border='0' cellpadding='0' cellspacing='0'>\n";
    echo "<tr>\n";
    if ($action == "add") {
        echo "<td align='left' width='30%' nowrap='nowrap' valign='top'><b>".$text['header-destination_ext-add']."</b></td>\n";
    }
    if ($action == "update") {
        echo "<td align='left' width='30%' nowrap='nowrap' valign='top'><b>".$text['header-destination_ext-edit']."</b></td>\n";
    }
    echo "<td width='70%' align='right' valign='top'>";
    echo "  <input type='button' class='btn' alt='".$text['button-back']."' onclick=\"window.location='destinations_ext.php'\" value='".$text['button-back']."'>";
    echo "  <input type='submit' class='btn' value='".$text['button-save']."'>\n";
    echo "</td>\n";
    echo "</tr>\n";
    echo "<tr>\n";
    echo "<td align='left' colspan='2'>\n";
    echo $text['description-destinations_ext']."<br /><br />\n";
    echo "</td>\n";
    echo "</tr>\n";

// Destination number enter
    echo "<tr>\n";
    echo "<td class='vncellreq' valign='top' align='left' nowrap='nowrap'>\n";
    echo "  ".$text['label-destination_ext_number']."\n";
    echo "</td>\n";
    echo "<td class='vtable' align='left'>\n";
    echo "  <input class='formfld' type='text' name='destination_ext_number' maxlength='255' value=\"" . escape($destination_ext_number) . "\" required='required'>\n";
    echo "<br />\n";
    echo $text['description-destination_ext_number']."\n";
    echo "</td>\n";
    echo "</tr>\n";

// Main number actions select

    echo "<tr id='tr_actions_main'>\n";
    echo "<td class='vncell' valign='top' align='left' nowrap='nowrap'>\n";
    echo "  ".$text['label-detail_action_main']."\n";
    echo "</td>\n";
    echo "<td class='vtable' align='left'>\n";

    echo "          <table width='52%' border='0' cellpadding='2' cellspacing='0'>\n";

    $x = 0;
    $order = 10;
    $dialplan_details = $destination_ext_dialplan_main_details;

    foreach($dialplan_details as $row) {

        $row = array_map('escape', $row);

        if ($row["dialplan_detail_tag"] != "condition") {
            if ($row["dialplan_detail_tag"] == "action" && $row["dialplan_detail_type"] == "set") {
                // Exclude all set's and lua's
                continue;
            }
            if ($row["dialplan_detail_type"] == "lua" && $destination_ext_silence_detect == 'true') {
                // Exclude lua's in a case of silence detect
                continue;
            }
            echo "              <tr>\n";
            echo "                  <td style='padding-top: 5px; padding-right: 3px; white-space: nowrap;'>\n";
            if (strlen($row['dialplan_detail_uuid']) > 0) {
                echo "  <input name='destination_ext_dialplan_main_details[".$x."][dialplan_detail_uuid]' type='hidden' value=\"".$row['dialplan_detail_uuid']."\">\n";
            }
            echo "  <input name='destination_ext_dialplan_main_details[".$x."][dialplan_detail_type]' type='hidden' value=\"".$row['dialplan_detail_type']."\">\n";
            echo "  <input name='destination_ext_dialplan_main_details[".$x."][dialplan_detail_order]' type='hidden' value=\"".$order."\">\n";

            $data = $row['dialplan_detail_data'];
            $label = explode("XML", $data);
            $divider = ($row['dialplan_detail_type'] != '') ? ":" : null;
            $detail_action = $row['dialplan_detail_type'].$divider.$row['dialplan_detail_data'];
            echo $destination->select('dialplan', 'destination_ext_dialplan_main_details['.$x.'][dialplan_detail_data]', $detail_action);
            echo "                  </td>\n";
            echo "                  <td class='list_control_icons' style='width: 25px;'>";
            if (strlen($row['destination_ext_uuid']) > 0) {
                echo                    "<a href='destination_ext_delete.php?id=".$row['destination_ext_uuid']."&destination_ext_uuid=".$row['destination_ext_uuid']."&a=delete' alt='delete' onclick=\"return confirm('".$text['confirm-delete']."')\">".$v_link_label_delete."</a>\n";
            }
            echo "                  </td>\n";
            echo "              </tr>\n";
            break;
        }
        $order = $order + 10;
        $x++;
    }
    echo "          </table>\n";
    echo "</td>\n";
    echo "</tr>\n";

// Extensions destination set

    echo "<tr id='tr_actions_ext'>\n";
    echo "<td class='vncell' valign='top' align='left' nowrap='nowrap'>\n";
    echo "  ".$text['label-detail_action_ext']."\n";
    echo "</td>\n";
    echo "<td class='vtable' align='left'>\n";

    echo "          <table width='52%' border='0' cellpadding='2' cellspacing='0'>\n";

    $x = 0;
    $order = 10;
    $dialplan_details = $destination_ext_dialplan_extensions_details;

    if (sizeof($dialplan_details) == 1) {

        $domain_uuid = $dialplan_details[0]['domain_uuid'];
        $destination_ext_domain = $_SESSION['domains'][$domain_uuid]['domain_name'];

        $dialplan_details = array();
        $dialplan_details[1] = array();
        $dialplan_details[1]['dialplan_detail_data'] = "transfer:$1 XML " . escape($destination_ext_domain);
        $dialplan_details[1]['dialplan_detail_tag'] = "action";
    }

    foreach($dialplan_details as $row) {

        $row = array_map('escape', $row);

        if ($row["dialplan_detail_tag"] != "condition") {
            if ($row["dialplan_detail_tag"] == "action" && $row["dialplan_detail_type"] == "set") {
                // Exclude all set's
                continue;
            }
            if ($row["dialplan_detail_type"] == "lua" && $destination_ext_silence_detect == 'true') {
                // Exclude lua's in a case of silence detect
                continue;
            }

            echo "              <tr>\n";
            echo "                  <td style='padding-top: 5px; padding-right: 3px; white-space: nowrap;'>\n";
            if (strlen($row['dialplan_detail_uuid']) > 0) {
                echo "  <input name='destination_ext_dialplan_extensions_details[".$x."][dialplan_detail_uuid]' type='hidden' value=\"".$row['dialplan_detail_uuid']."\">\n";
            }
            echo "  <input name='destination_ext_dialplan_extensions_details[".$x."][dialplan_detail_type]' type='hidden' value=\"".$row['dialplan_detail_type']."\">\n";
            echo "  <input name='destination_ext_dialplan_extensions_details[".$x."][dialplan_detail_order]' type='hidden' value=\"".$order."\">\n";

            $data = $row['dialplan_detail_data'];
            $label = explode("XML", $data);
            $divider = ($row['dialplan_detail_type'] != '') ? ":" : null;
            $detail_action = $row['dialplan_detail_type'].$divider.$row['dialplan_detail_data'];
            echo $destination->select('dialplan', 'destination_ext_dialplan_extensions_details['.$x.'][dialplan_detail_data]', $detail_action);
            echo "                  </td>\n";
            echo "                  <td class='list_control_icons' style='width: 25px;'>";
            if (strlen($row['destination_ext_uuid']) > 0) {
                echo                    "<a href='destination_ext_delete.php?id=".$row['destination_ext_uuid']."&destination_ext_uuid=".$row['destination_ext_uuid']."&a=delete' alt='delete' onclick=\"return confirm('".$text['confirm-delete']."')\">".$v_link_label_delete."</a>\n";
            }
            echo "                  </td>\n";
            echo "              </tr>\n";
            break;
        }
        $order = $order + 10;
        $x++;
    }
    echo "          </table>\n";
    echo "</td>\n";
    echo "</tr>\n";


// Invalid destiantions set

    echo "<tr id='tr_actions_invalid'>\n";
    echo "<td class='vncell' valign='top' align='left' nowrap='nowrap'>\n";
    echo "  ".$text['label-detail_action_invalid']."\n";
    echo "</td>\n";
    echo "<td class='vtable' align='left'>\n";

    echo "          <table width='52%' border='0' cellpadding='2' cellspacing='0'>\n";

    $x = 0;
    $order = 10;
    $dialplan_details = $destination_ext_dialplan_invalid_details;

    if (sizeof($dialplan_details) == 1) {

        $domain_uuid = $dialplan_details[0]['domain_uuid'];
        $destination_ext_domain = $_SESSION['domains'][$domain_uuid]['domain_name'];

        $dialplan_details = array();
        $dialplan_details[1] = array();
        $dialplan_details[1]['dialplan_detail_data'] = "hangup";
        $dialplan_details[1]['dialplan_detail_tag'] = "action";
    }

    foreach($dialplan_details as $row) {

        $row = array_map('escape', $row);

        if ($row["dialplan_detail_tag"] != "condition") {
            if ($row["dialplan_detail_tag"] == "action" && $row["dialplan_detail_type"] == "set") {
                // Exclude all set's.
                continue;
            }
            echo "              <tr>\n";
            echo "                  <td style='padding-top: 5px; padding-right: 3px; white-space: nowrap;'>\n";
            if (strlen($row['dialplan_detail_uuid']) > 0) {
                echo "  <input name='destination_ext_dialplan_invalid_details[".$x."][dialplan_detail_uuid]' type='hidden' value=\"".$row['dialplan_detail_uuid']."\">\n";
            }
            echo "  <input name='destination_ext_dialplan_invalid_details[".$x."][dialplan_detail_type]' type='hidden' value=\"".$row['dialplan_detail_type']."\">\n";
            echo "  <input name='destination_ext_dialplan_invalid_details[".$x."][dialplan_detail_order]' type='hidden' value=\"".$order."\">\n";

            $data = $row['dialplan_detail_data'];
            $label = explode("XML", $data);
            $divider = ($row['dialplan_detail_type'] != '') ? ":" : null;
            $detail_action = $row['dialplan_detail_type'].$divider.$row['dialplan_detail_data'];
            echo $destination->select('dialplan', 'destination_ext_dialplan_invalid_details['.$x.'][dialplan_detail_data]', $detail_action);
            echo "                  </td>\n";
            echo "                  <td class='list_control_icons' style='width: 25px;'>";
            if (strlen($row['destination_ext_uuid']) > 0) {
                echo                    "<a href='destination_ext_delete.php?id=".$row['destination_ext_uuid']."&destination_ext_uuid=".$row['destination_ext_uuid']."&a=delete' alt='delete' onclick=\"return confirm('".$text['confirm-delete']."')\">".$v_link_label_delete."</a>\n";
            }
            echo "                  </td>\n";
            echo "              </tr>\n";
            break;
        }
        $order = $order + 10;
        $x++;
    }
    echo "          </table>\n";
    echo "</td>\n";
    echo "</tr>\n";

    if (permission_exists('destination_domain')) {
        echo "<tr>\n";
        echo "<td class='vncell' valign='top' align='left' nowrap='nowrap'>\n";
        echo "  ".$text['label-domain']."\n";
        echo "</td>\n";
        echo "<td class='vtable' align='left'>\n";
        echo "    <select class='formfld' name='domain_uuid' id='destination_domain' onchange='context_control();'>\n";
        if (strlen($domain_uuid) == 0) {
            echo "    <option value='' selected='selected'>".$text['select-global']."</option>\n";
        }
        else {
            echo "    <option value=''>".$text['select-global']."</option>\n";
        }
        foreach ($_SESSION['domains'] as $row) {
            $row = array_map('escape', $row);

            if ($row['domain_uuid'] == $domain_uuid) {
                echo "    <option value='".$row['domain_uuid']."' selected='selected'>".$row['domain_name']."</option>\n";
            }
            else {
                echo "    <option value='".$row['domain_uuid']."'>".$row['domain_name']."</option>\n";
            }
        }
        echo "    </select>\n";
        echo "<br />\n";
        echo $text['description-domain_name']."\n";
        echo "</td>\n";
        echo "</tr>\n";
    }
    else {
        echo "<input type='hidden' name='domain_uuid' value='" . escape($domain_uuid) ."'>\n";
    }

    // CallerID name prepend
    echo "<tr>\n";
    echo "<td class='vncell' valign='top' align='left' nowrap='nowrap'>\n";
    echo "  ".$text['label-destination_ext_callerid_name_prepend']."\n";
    echo "</td>\n";
    echo "<td class='vtable' align='left'>\n";
    echo "  <input class='formfld' type='text' name='destination_ext_callerid_name_prepend' id='destination_ext_callerid_name_prepend' maxlength='255' value=\"" . escape($destination_ext_callerid_name_prepend) . "\">\n";
    echo "<br />\n";
    echo $text['description-destination_ext_callerid_name_prepend']."\n";
    echo "</td>\n";
    echo "</tr>\n";

    // CallerID number prepend
    echo "<tr>\n";
    echo "<td class='vncell' valign='top' align='left' nowrap='nowrap'>\n";
    echo "  ".$text['label-destination_ext_callerid_number_prepend']."\n";
    echo "</td>\n";
    echo "<td class='vtable' align='left'>\n";
    echo "  <input class='formfld' type='text' name='destination_ext_callerid_number_prepend' id='destination_ext_callerid_number_prepend' maxlength='255' value=\"" . escape($destination_ext_callerid_name_prepend) . "\">\n";
    echo "<br />\n";
    echo $text['description-destination_ext_callerid_number_prepend']."\n";
    echo "</td>\n";
    echo "</tr>\n";

    // Destination variable
    echo "<tr>\n";
    echo "<td class='vncell' valign='top' align='left' nowrap='nowrap'>\n";
    echo "  ".$text['label-destination_ext_variable']."\n";
    echo "</td>\n";
    echo "<td class='vtable' align='left'>\n";
    echo "  <input class='formfld' type='text' name='destination_ext_variable' id='destination_ext_variable' maxlength='255' value=\"" . escape($destination_ext_variable) . "\">\n";
    echo "<br />\n";
    echo $text['description-destination_ext_variable']."\n";
    echo "</td>\n";
    echo "</tr>\n";

     // Alternate Destination variable destination
    echo "<tr>\n";
    echo "<td class='vncell' valign='top' align='left' nowrap='nowrap'>\n";
    echo "  ".$text['label-destination_ext_variable_no_extension']."\n";
    echo "</td>\n";
    echo "<td class='vtable' align='left'>\n";
    echo "  <input class='formfld' type='text' name='destination_ext_variable_no_extension' id='destination_ext_variable_no_extension' maxlength='255' value=\"" . escape($destination_ext_variable_no_extension) . "\">\n";
    echo "<br />\n";
    echo $text['description-destination_ext_variable_no_extension']."\n";
    echo "</td>\n";
    echo "</tr>\n";

    // Silence detect
    if ($destination_ext_silence_detect_enabled) {
        echo "<tr>\n";
        echo "<td class='vncell' valign='top' align='left' nowrap='nowrap'>\n";
        echo "  ".$text['label-destination_ext_silence_detect']."\n";
        echo "</td>\n";
        echo "<td class='vtable' align='left'>\n";
        echo "  <select class='formfld' name='destination_ext_silence_detect'>\n";
        if ($destination_ext_silence_detect == 'true') {
            $selected[1] = "selected='selected'";
        } else {
            $selected[2] = "selected='selected'";
        }
        echo "  <option value='true' ".$selected[1].">".$text['label-true']."</option>\n";
        echo "  <option value='false' ".$selected[2].">".$text['label-false']."</option>\n";
        unset($selected);
        echo "  </select>&ensp;" . escape($destination_ext_silence_detect_algo) ."\n";
        echo "<br />\n";
        echo $text['description-destination_ext_silence_detect']."\n";
        echo "</td>\n";
        echo "</tr>\n";
    }

    // Enabled / Disabled
    echo "<tr>\n";
    echo "<td class='vncell' valign='top' align='left' nowrap='nowrap'>\n";
    echo "  ".$text['label-destination_ext_enabled']."\n";
    echo "</td>\n";
    echo "<td class='vtable' align='left'>\n";
    echo "  <select class='formfld' name='destination_ext_enabled'>\n";
    switch ($destination_ext_enabled) {
        case "true" :   $selected[1] = "selected='selected'";   break;
        case "false" :  $selected[2] = "selected='selected'";   break;
    }
    echo "  <option value='true' ".$selected[1].">".$text['label-true']."</option>\n";
    echo "  <option value='false' ".$selected[2].">".$text['label-false']."</option>\n";
    unset($selected);
    echo "  </select>\n";
    echo "<br />\n";
    echo $text['description-destination_ext_enabled']."\n";
    echo "</td>\n";
    echo "</tr>\n";

    echo "<tr>\n";
    echo "<td class='vncell' valign='top' align='left' nowrap='nowrap'>\n";
    echo "  ".$text['label-destination_ext_description']."\n";
    echo "</td>\n";
    echo "<td class='vtable' align='left'>\n";
    echo "  <input class='formfld' type='text' name='destination_ext_description' maxlength='255' value=\"" . escape($destination_ext_description) . "\">\n";
    echo "<br />\n";
    echo $text['description-destination_ext_description']."\n";
    echo "</td>\n";
    echo "</tr>\n";
    echo "  <tr>\n";
    echo "      <td colspan='2' align='right'>\n";
    if ($action == "update") {
        echo "      <input type='hidden' name='db_destination_ext_number' value='" . escape($destination_ext_number) . "'>\n";
        echo "      <input type='hidden' name='destination_ext_dialplan_main_uuid' value='" . escape($destination_ext_dialplan_main_uuid) . "'>\n";
        echo "      <input type='hidden' name='destination_ext_dialplan_extensions_uuid' value='" . escape($destination_ext_dialplan_extensions_uuid) . "'>\n";
        echo "      <input type='hidden' name='destination_ext_uuid' value='" . escape($destination_ext_uuid) . "'>\n";
    }
    echo "          <br>";
    echo "          <input type='submit' class='btn' value='".$text['button-save']."'>\n";
    echo "      </td>\n";
    echo "  </tr>";
    echo "</table>";
    echo "<br><br>";
    echo "</form>";

//include the footer
    require_once "resources/footer.php";

?>
