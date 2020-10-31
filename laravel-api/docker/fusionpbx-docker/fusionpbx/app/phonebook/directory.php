<?php

/*

Main script that responsible for generating phonebook.

Can accept following parameters

 - vendor - look on later switch statement for supported versions
 - gid, gid_X - you can specify multiple groups with _<digit> suffix for 'gid'. Also 'gid' can accept special keyword 'directory', in this case it can 
				provide data from actual extensions along with Directory names. 
 - call_group - will affect only if one of 'gid's is 'directory. You can also filter by call_group setting.
*/

require_once "root.php";
require_once "resources/require.php";

$is_auth = isset($_SESSION['phonebook']['auth']['text']) ? filter_var($_SESSION['phonebook']['auth']['text'], FILTER_VALIDATE_BOOLEAN) : 'true';
$vendor  = isset($_REQUEST["vendor"]) ? strtolower(escape(check_str($_REQUEST["vendor"]))) : 'yealink';

$groupid_array_keys = preg_grep('/^(gid)|(gid_\d+)$/', array_keys($_REQUEST));

if ($is_auth) {
	// Check auth (adding more security)
	require_once "resources/check_auth.php";

	if (!permission_exists('phonebook_phone_access')) {
		echo "Access denied";
		exit;
	}
	
} else {
	if (count($groupid_array_keys) == 0 or in_array('directory', $groupid_array_keys)) {
		// Can't get all of phonebook or directory without specifying auth.
		echo "Access denied";
		exit;
	}
}

$result = array();

if (count($groupid_array_keys) == 0) {
	$sql = "SELECT name, phonenumber, phonebook_desc";
	$sql .= " FROM v_phonebook";
	$sql .= " WHERE domain_uuid = '$domain_uuid'";
	$prep_statement = $db->prepare(check_sql($sql));
	$prep_statement->execute();
	$result = $prep_statement->fetchAll();
	unset ($prep_statement, $sql);
} else {

	foreach ($groupid_array_keys as $groupid_key) {
		
		$groupid = strtolower(check_str($_REQUEST[$groupid_key]));
		if ($groupid == 'directory') {
			// Select Directory First name/Last name, if empty - use extension column
			$call_group = $groupid = isset($_REQUEST["call_group"]) ? strtolower(check_str($_REQUEST["call_group"])) : False;

			$sql = "SELECT COALESCE(NULLIF(TRIM(CONCAT(directory_first_name, ' ', directory_last_name)), ''), description ,extension)  AS name,";
			$sql .= " extension AS phonenumber, description AS phonebook_desc";
			$sql .= " FROM v_extensions";
			$sql .= " WHERE directory_visible = 'true'";
			if ($call_group) {
				$sql .= " AND call_group = '" . $call_group . "'";
			}
			$sql .= " AND domain_uuid = '" . $domain_uuid . "'";
		} else {
			$sql = "SELECT DISTINCT v_phonebook.phonebook_uuid,";
			$sql .= " v_phonebook.name, v_phonebook.phonenumber, v_phonebook.phonebook_desc FROM v_phonebook ";
			$sql .= " INNER JOIN v_phonebook_to_groups ON";
			$sql .= " v_phonebook.phonebook_uuid = v_phonebook_to_groups.phonebook_uuid";
			$sql .= " WHERE v_phonebook.domain_uuid = '" . $domain_uuid . "'";
			$sql .= " AND v_phonebook_to_groups.group_uuid = '" . $groupid . "'";
		}
		$prep_statement = $db->prepare(check_sql($sql));
		$prep_statement->execute();
		$result = array_merge($prep_statement->fetchAll(), $result);
		unset ($prep_statement, $sql);
	}
}

$response = '';

switch ($vendor) {
	case 'yealink': // Yealink phonebooks
		$response .= '<PhonebookIPPhoneDirectory>' . "\n";

		foreach($result as $row) {
			$row = array_map('escape', $row);

			$response .= '  <DirectoryEntry>' . "\n";
			$response .= '    <Name>' . $row['name'] . '</Name>' . "\n";
			$response .= '    <Telephone>' . $row['phonenumber'] . '</Telephone>' . "\n";
			$response .= '  </DirectoryEntry>' . "\n";
		}

		$response .= '</PhonebookIPPhoneDirectory>' . "\n";
		break;
	
	case 'snom': // Snom tbook

		$snom_embedded_settings = isset($_SESSION['phonebook']['snom_embedded_settings']['text']) ? filter_var($_SESSION['phonebook']['snom_embedded_settings']['text'], FILTER_VALIDATE_BOOLEAN) : True;
		if (!$snom_embedded_settings) {
			$response .= '<?xml version="1.0" encoding="utf-8"?>' . "\n";
		}

		$response .= '<tbook complete="true">' . "\n";

		foreach ($result as $index => $value) {
			$row = array_map('escape', $value);

			$response .= '<item context="active" type="none" index="'. $index .'">' . "\n";
			$response .= '<name>' . $value['name'] . '</name>' . "\n";
			$response .= '<number>' . $value['phonenumber'] . '</number>' . "\n";
			$response .= '</item>' . "\n";
		}

		$response .= '</tbook>' . "\n";
        break;
        
	case 'cisco_xml_directory_service': // Cisco XML directory - one is working for me
		$response .= '<?xml version="1.0" encoding="utf-8" ?>' . "\n";
		$response .= ' <CiscoIPPhoneDirectory>' . "\n";
		$response .= '  <Title>Phonebook</Title>' . "\n";
		$response .= '  <Prompt>Choose entry</Prompt>' . "\n";
		foreach($result as $row) {
			$row = array_map('escape', $row);

			$response .= '    <DirectoryEntry>' . "\n";
			$response .= '     <Name>' . $row['name'] . '</Name>' . "\n";
			$response .= '     <Telephone>' . $row['phonenumber'] . '</Telephone>' . "\n";
			$response .= '    </DirectoryEntry>' . "\n";
		}
		$response .= ' </CiscoIPPhoneDirectory>' . "\n";
        break;
        
	case 'cisco_paddrbook': // PAddr book. Couldn't get it to work
		$response .= '<paddrbook>' . "\n";
		foreach($result as $row) {
			$row = array_map('escape', $row);
			
			$response .= ' <entry>' . "\n";
			$response .= '  <name>' . $row['name'] . '<name>' . "\n";
			$response .= '  <workPhone>' . $row['phonenumber'] . '<workPhone>' . "\n";
			$response .= '  <ringToneID>1</ringToneID>' . "\n";
			$response .= ' </entry>' . "\n";
		}
		$response .= '</paddrbook>' . "\n";
        break;
        
    case 'polycom': // Polycom phonebook. It's actually up to template to imitate XXXXXX-directory.xml
        
        // Is show contacts on main screen as speed dial
        $is_speed_dial = isset($_SESSION['phonebook']['polycom_speed_dial_show']['text']) ? filter_var($_SESSION['phonebook']['polycom_speed_dial_show']['text'], FILTER_VALIDATE_BOOLEAN) : False;
        
		$response .= '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
		$response .= '<directory>' . "\n";
		$response .= '	<item_list>' . "\n";
		foreach($result as $index => $value) {
			$row = array_map('escape', $value);
			$response .= '		<item>' . "\n";
			$response .= '			<ln>' . $value['name'] . "</ln>\n";
            $response .= '			<ct>' . $value['phonenumber'] . "</ct>\n";
            if ($is_speed_dial) {
                $response .= '			<sd>' . ($index + 1) . "</sd>\n";
            }
			$response .= '		</item>' . "\n";
		}
		$response .= '	</item_list>' . "\n";
		$response .= '</directory>' . "\n";
		break;
}


header("Content-type: text/xml; charset=utf-8");
header("Content-Length: ".strlen($response));

echo $response;

?>