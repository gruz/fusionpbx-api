<?php

$text['title-sms_message']['en-us'] = "SMS";

$text['title-sms_routing']['en-us'] = "SMS Routing";

$text['header-sms_message']['en-us'] = "SMS";

$text['description-sms_messages']['en-us'] = "Received and sent SMS messages";

$text['description-sms_routing']['en-us'] = "SMS send and receive rules. If multiple rules match same SMS, only one of will apply";

$text['description-sms_routing_source']['en-us'] = "Template-style pattern to match source of SMS message. See description below";

$text['description-sms_routing_destination']['en-us'] = "Template-style pattern to match destination of SMS message. See description below";

$text['description-sms_routing_target_type']['en-us'] = "Use CARRIER if you want SMS matching pattern to be forwarded to carrier via web hook or INTERNAL if you want it to be delivered to extension via SIP SIMPLE";

$text['description-sms_routing_target_details']['en-us'] = "In a case of CARRIER target, enter name of carrier, in a case of INTERNAL - extension number SMS would be delivered through";

$text['description-sms_routing_number_translation_source']['en-us'] = "Number translation to be applied to source number of SMS message before processing";

$text['description-sms_routing_number_translation_destination']['en-us'] = "Number translation to be applied to destination number of SMS message before processing";

$text['description-sms_routing_enabled']['en-us'] = "Enable/Disable this routing rule";

$text['description-sms_routing_description']['en-us'] = "Description of this routing rule";

$text['button-sms_routing']['en-us'] = "Routing";

$text['button-sms_messages']['en-us'] = "SMS List";

$text['label-sms_message_timestamp']['en-us'] = "Time";

$text['label-sms_message_from']["en-us"] = "From";

$text['label-sms_message_to']['en-us'] = "To";

$text['label-sms_message_text']['en-us'] = "Text";

$text['label-sms_message_direction']['en-us'] = "Direction";

$text['label-sms_message_status']['en-us'] = "Status";

$text['label-sms_routing_source']['en-us'] = "Source";

$text['label-sms_routing_destination']['en-us'] = "Destination";

$text['label-sms_routing_target_type']['en-us'] = "Target type";

$text['label-sms_routing_target_type_carrier']['en-us'] = "Carrier";

$text['label-sms_routing_target_type_internal']['en-us'] = "Internal";

$text['label-sms_routing_target_details']['en-us'] = "Target details";

$text['label-sms_routing_number_translation_source']['en-us'] = "Source Number Translation";

$text['label-sms_routing_number_translation_destination']['en-us'] = "Destination Number Translation";

$text['label-sms_routing_enabled']['en-us'] = "Enabled";

$text['label-sms_routing_description']['en-us'] = "Description";

$text['label-update-complete']['en-us'] = "Add Complete";

$text['label-delete-complete']['en-us'] = "Delete Complete";

$text['label-delete-error']['en-us'] = "Delete Error!";

$text['label-edit-add']['en-us'] = "Add SMS Routing rule";

$text['label-edit-edit']['en-us'] = "Edit SMS Routing rule";

$text['label-add-note']['en-us'] = "Add SMS Routing rule";

$text['label-edit-note']['en-us'] = "Edit SMS Routing rule";

$text['description-sms_routing_edit']['en-us'] = "Templates support ranges and wildcards. Actually it's limited regexes<br>";
$text['description-sms_routing_edit']['en-us'] .= "[A-B] matches any digit within A-B range<br>";
$text['description-sms_routing_edit']['en-us'] .= "'X' matches any digit one time<br>";
$text['description-sms_routing_edit']['en-us'] .= "'*' matches anything<br>";
$text['description-sms_routing_edit']['en-us'] .= "'^'  matches start of the string<br>";
$text['description-sms_routing_edit']['en-us'] .= "'$'  matches end of the string<br>";
$text['description-sms_routing_edit']['en-us'] .= "Examples:<br>";
$text['description-sms_routing_edit']['en-us'] .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;^[1-2]X matches numbers 10 - 29 <br>";
$text['description-sms_routing_edit']['en-us'] .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;^XXX or ^XXX*$ matches numbers with 3 digits and more<br>";
$text['description-sms_routing_edit']['en-us'] .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;^XXX$ matches numbers with exact 3 digits <br>";
$text['description-sms_routing_edit']['en-us'] .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;^*[45]$ matches numbers that ends with 4 or 5 <br>";

?>