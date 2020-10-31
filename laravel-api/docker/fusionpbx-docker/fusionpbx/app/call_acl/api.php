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
    Igor Olhovskiy <igorolhovskiy@gmail.com>
*/

/*
API enpoint to add call ACL's via requests.

domain.name/app/call_acl/api.php?action=allow&source=4321&destination=1234&order=10&key=<user_api_key>
source - source
destination - destination
order - order
action - Alllow/Reject
method - add/delete. By default - add
uuid - uuid of deleted value. Valid for delete method
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

//check permissions
if (permission_exists('call_acl_edit') || permission_exists('call_acl_add')) {
    //access granted
} else {
    send_answer('401', "Access denied");
    exit;
}

$parameters = count($_POST) > 0 ? $_POST : $_GET;

if (count($parameters) > 0) {
    $action = isset($parameters["action"]) ? strtolower(check_str($parameters["action"])) : False;
    $destination = isset($parameters["destination"]) ? check_str($parameters["destination"]) : False;
    $order = isset($parameters["order"]) ? check_str($parameters["order"]) : False;
    $source = isset($parameters["source"]) ? check_str($parameters["source"]) : False;
    $name = isset($parameters["name"]) ? check_str($parameters["name"]) : "$action from $source to $destination";
    $enabled = isset($parameters["enabled"]) ? check_str($parameters["enabled"]) : 'true';
    $method = isset($parameters["method"]) ? check_str($parameters["method"]) : 'add';
    $uuid = isset($parameters["uuid"]) ? check_str($parameters["uuid"]) : False;
}

if ($method == 'add') {
    if (!($action && $destination && $order && $source)) {
        send_answer('406', 'Not all parameters found');
        return;
    }

    if (!is_numeric($order) || intval($order) != $order) {
        send_answer('406', 'Order is not numeric');
        return;
    }

    if ($action != 'allow' && $action != 'reject') {
        send_answer('406', "$action is not valid action");
        return;
    }

    $sql = "INSERT INTO v_call_acl ";
    $sql .= "(";
    $sql .= "domain_uuid, ";
    $sql .= "call_acl_uuid, ";
    $sql .= "call_acl_order, ";
    $sql .= "call_acl_name, ";
    $sql .= "call_acl_source, ";
    $sql .= "call_acl_destination, ";
    $sql .= "call_acl_action, ";
    $sql .= "call_acl_enabled ";
    $sql .= ") ";
    $sql .= "VALUES ";
    $sql .= "(?, ?, ?, ?, ?, ?, ?, ?)";

    $uuid = uuid();

    $insert_array = array(
        'domain_uuid' => $_SESSION['domain_uuid'],
        'call_acl_uuid' => $uuid,
        'call_acl_order' => $order,
        'call_acl_name' => $name,
        'call_acl_source' => $source,
        'call_acl_destination' => $destination,
        'call_acl_action' => $action,
        `call_acl_enabled` => $enabled
    );

    $prep_statement = $db->prepare(check_sql($sql));
    if (!$prep_statement->execute(array_values($insert_array))) {
        send_answer('500', json_encode($prep_statement->errorInfo()));
    } else {
        send_answer('200', $uuid);
    }
    exit;
}

if ($method == 'delete') {
    if (!$uuid and strlen($source) == 0) {
        send_answer('406', 'Not all parameters found');
        return;
    }

    if ($uuid) {
        $sql = "DELETE FROM v_call_acl";
        $sql .= " WHERE call_acl_uuid = :call_acl_uuid";

        $prep_statement = $db->prepare(check_sql($sql));

        $prep_statement->bindValue('call_acl_uuid', $uuid);

        if (!$prep_statement->execute()) {
            send_answer('500', json_encode($prep_statement->errorInfo()));
        } else {
            send_answer('200', $uuid);
        }
    } elseif (strlen($source) > 0) {
        $sql = "DELETE FROM v_call_acl";
        $sql .= " WHERE call_acl_source = :call_acl_source ";
        $sql .= " AND domain_uuid = :domain_uuid";

        $prep_statement = $db->prepare(check_sql($sql));
        $prep_statement->bindValue('call_acl_source', $source);
        $prep_statement->bindValue('domain_uuid', $_SESSION['domain_uuid']);
        if (!$prep_statement->execute()) {
            send_answer('500', json_encode($prep_statement->errorInfo()));
        } else {
            send_answer('200', "OK");
        }
    }
    exit;
}

send_answer('404', 'Method not found');
?>