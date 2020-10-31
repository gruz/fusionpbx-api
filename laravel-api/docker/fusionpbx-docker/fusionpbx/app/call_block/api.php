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
    Luis Daniel Lucio Quiroz <dlucio@okay.com.mx>
    Igor Olhovskiy <igorolhovskiy@gmail.com>

	Call Block is written by Gerrit Visser <gerrit308@gmail.com>
*/

/*
API enpoint to add call blocks via requests.

domain.name/app/call_block/api.php?action=add&number=4321&key=<user_api_key>
action - add
number - number to block
call_block_name - name (if no - auto)
call_block_action - Reject (default), Hold, Busy
call_block_enabled - True (default), False
*/

//includes
require_once "root.php";
require_once "resources/require.php";
require_once "resources/check_auth.php";

// Used functions

function send_answer($code, $message) {
    $status = array(
        'code' => $code,
        'message' => $message
    );
    $status = json_encode($status);
    echo $status;
}

function format_number($number) {

    return preg_replace( '/[^0-9]/', '', $number);
}

//check permissions
if (permission_exists('call_block_edit') || permission_exists('call_block_add')) {
    //access granted
} else {
    send_answer('401', "Access denied");
    exit;
}

$parameters = count($_POST) > 0 ? $_POST : $_GET;

if (count($parameters) > 0) {
    $action = isset($parameters["action"]) ? check_str($parameters["action"]) : False;
    $call_block_number = isset($parameters["number"]) ? check_str($parameters["number"]) : False;

    $call_block_name = isset($parameters["call_block_name"]) ? check_str($parameters["call_block_name"]) : 'API ' . $call_block_number;
    $call_block_action = isset($parameters["call_block_action"]) ? check_str($parameters["call_block_action"]) : 'Reject' ;
    $call_block_enabled = isset($parameters["call_block_enabled"]) ? check_str($parameters["call_block_enabled"]) : 'true';
}

if (!$action) {
    send_answer('406', 'Action not found');
    return;
}

if (!$call_block_number) {
    send_answer('406', 'Number not found');
    return;
}

if ($action == "add") {

    $call_block_number = format_number($call_block_number);
    $call_block_numbers = [$call_block_number, "+" . $call_block_number];

    foreach ($call_block_numbers as $call_block_number) {
        $sql = "INSERT INTO v_call_block ";
        $sql .= "(";
        $sql .= "domain_uuid, ";
        $sql .= "call_block_uuid, ";
        $sql .= "call_block_name, ";
        $sql .= "call_block_number, ";
        $sql .= "call_block_count, ";
        $sql .= "call_block_action, ";
        $sql .= "call_block_enabled, ";
        $sql .= "date_added ";
        $sql .= ") ";
        $sql .= "VALUES ";
        $sql .= "(?, ?, ?, ?, ?, ?, ?, ?)";

        $insert_array = array(
            'domain_uuid' => $domain_uuid,
            'call_block_uuid' => uuid(),
            'call_block_name' => $call_block_name,
            'call_block_number' => $call_block_number,
            'call_block_count' => 0,
            'call_block_action' => $call_block_action,
            'call_block_enabled' => $call_block_enabled,
            'date_added' => time(),
        );

        $prep_statement = $db->prepare(check_sql($sql));
        if (!$prep_statement->execute(array_values($insert_array))) {
            send_answer('500', json_encode($prep_statement->errorInfo()));
            return;
        }
    }
    send_answer('200', 'Number added');
} else {
    send_answer('404', "Action $action not found");
}

?>