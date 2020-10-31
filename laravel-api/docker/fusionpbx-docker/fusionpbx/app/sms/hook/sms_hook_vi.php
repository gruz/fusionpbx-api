<?php

include "../root.php";
require_once "resources/require.php";

$domain_name = $_SESSION['domain_name'];

$ip_addr = $_SERVER['REMOTE_ADDR'];

$sms_acl = new check_sms_acl($db, $domain_name);

if (!$sms_acl->check($ip_addr)) {
    echo "Access denied.";
    return;
}

$sms_message = json_decode(file_get_contents('php://input'), JSON_OBJECT_AS_ARRAY);

if ($sms_message['messageType'] != "SMS") {
    echo "Only SMS supported.";
    return;
}

$sms_message_from = $sms_message['from'];
$sms_message_to = $sms_message['to'];
$sms_message_text = addslashes($sms_message['text']);

$fp = event_socket_create($_SESSION['event_socket_ip_address'], $_SESSION['event_socket_port'], $_SESSION['event_socket_password']);

if (!$fp) {
    echo "Connection to event socket failed.";
    return;
}
$switch_cmd = "api luarun app_custom.lua sms -s external -f " . $sms_message_from . " -t " . $sms_message_to . " -m '" . $sms_message_text . "'";

$sms_send = trim(event_socket_request($fp, $switch_cmd));

echo $sms_send;
?>