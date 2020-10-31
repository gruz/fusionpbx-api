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
	Copyright (C) 2016 All Rights Reserved.

*/

//includes
	require_once "root.php";
	require_once "resources/require.php";

//check permissions
	require_once "resources/check_auth.php";
	if (permission_exists('device_key_add') || permission_exists('device_key_edit')) {
		//access granted
	}
	else {
		echo "access denied";
		exit;
	}

//add multi-lingual support
	$language = new text;
	$text = $language->get($_SESSION['domain']['language']['code'], 'app/devices');

//include the device class
	//require_once "app/devices/resources/classes/device.php";

//get the vendor functions
	$sql = "SELECT v.name as vendor_name, f.name, f.value ";
	$sql .= "FROM v_device_vendors as v, v_device_vendor_functions as f ";
	$sql .= "WHERE v.device_vendor_uuid = f.device_vendor_uuid ";
	$sql .= "AND f.device_vendor_function_uuid in ";
	$sql .= "(";
	$sql .= "	SELECT device_vendor_function_uuid FROM v_device_vendor_function_groups ";
	$sql .= "	WHERE device_vendor_function_uuid = f.device_vendor_function_uuid ";
	$sql .= "	AND ( ";
	if (is_array($_SESSION['groups'])) {
		$x = 0;
		foreach($_SESSION['groups'] as $row) {
			if ($x == 0) {
				$sql .= "		group_name = '".$row['group_name']."' ";
			}
			else {
				$sql .= "		or group_name = '".$row['group_name']."' ";
			}
			$x++;
		}
	}
	$sql .= "	) ";
	$sql .= ") ";
	$sql .= "AND v.enabled = 'true' ";
	$sql .= "AND f.enabled = 'true' ";
	$sql .= "ORDER BY v.name ASC, f.name ASC ";
	$prep_statement = $db->prepare(check_sql($sql));
	$prep_statement->execute();
	$vendor_functions = $prep_statement->fetchAll(PDO::FETCH_NAMED);

//add or update the database
	if (count($_POST) > 0 && strlen($_POST["persistformvar"]) == 0) {

		//add or update the database
			if ($_POST["persistformvar"] != "true") {

				//get device
					$sql = "SELECT device_uuid, device_profile_uuid FROM v_devices ";
					$sql .= "WHERE device_user_uuid = '".$_SESSION['user_uuid']."' ";
					$prep_statement = $db->prepare(check_sql($sql));
					$prep_statement->execute();
					$row = $prep_statement->fetch(PDO::FETCH_NAMED);
					$device_uuid = $row['device_uuid'];
					$device_profile_uuid = $row['device_profile_uuid'];
					unset($row);

				//get device profile keys
					if (isset($device_profile_uuid)) {
						$sql = "SELECT * FROM v_device_keys ";
						$sql .= "WHERE device_profile_uuid = '".$device_profile_uuid."' ";
						$prep_statement = $db->prepare(check_sql($sql));
						$prep_statement->execute();
						$device_profile_keys = $prep_statement->fetchAll(PDO::FETCH_NAMED);
						unset($sql,$prep_statement);
					}

				//get device keys
					if (isset($device_uuid)) {
						$sql = "SELECT * FROM v_device_keys ";
						$sql .= "WHERE device_uuid = '".$device_uuid."' ";
						$prep_statement = $db->prepare(check_sql($sql));
						$prep_statement->execute();
						$device_keys = $prep_statement->fetchAll(PDO::FETCH_NAMED);
						unset($sql,$prep_statement);
					}

				//create a list of protected keys - device keys
					if (is_array($device_keys)) {
						foreach($device_keys as $row) {
							//determine if the key is allowed
								$device_key_authorized = false;
								foreach($vendor_functions as $function) {
									if ($function['vendor_name'] == $row['device_key_vendor'] && $function['value'] == $row['device_key_type']) {
										$device_key_authorized = true;
									}
								}
							//add the protected keys
								if (!$device_key_authorized) {
									$protected_keys[$row['device_key_id']] = 'true';
								}
							//add to protected
								if ($row['device_key_protected'] == "true") {
									$protected_keys[$row['device_key_id']] = 'true';
								}
						}
					}
				//create a list of protected keys - device proile keys
					if (is_array($device_profile_keys)) {
						foreach($device_profile_keys as $row) {
							//determine if the key is allowed
								$device_key_authorized = false;
								if (is_array($vendor_functions)) {
									foreach($vendor_functions as $function) {
										if ($function['vendor_name'] == $row['device_key_vendor'] && $function['value'] == $row['device_key_type']) {
											$device_key_authorized = true;
										}
									}
								}
							//add the protected keys
								if (!$device_key_authorized) {
									$protected_keys[$row['device_key_id']] = 'true';
								}
						}
					}

				//remove the keys the user is not allowed to edit based on the authorized vendor keys
					$x=0;
					if (is_array($_POST['device_keys'])) {
						foreach($_POST['device_keys'] as $row) {
							//loop through the authorized vendor functions
								if ($protected_keys[$row['device_key_id']] == "true") {
									unset($_POST['device_keys'][$x]);
								}
							//increment the row id
								$x++;
						}
					}

				//add or update the device keys
					if (is_array($_POST['device_keys'])) {
						foreach ($_POST['device_keys'] as &$row) {

							//validate the data
								$save = true;
								//if (!is_uuid($row["device_key_uuid"])) { $save = false; }
								if (isset($row["device_key_id"])) {
									if (!is_numeric($row["device_key_id"])) { $save = false; echo $row["device_key_id"]." id "; }
								}
								if (strlen($row["device_key_type"]) > 25) { $save = false; echo "type "; }
								if (strlen($row["device_key_value"]) > 25) { $save = false; echo "value "; }
								if (strlen($row["device_key_label"]) > 25) { $save = false; echo "label "; }

							//escape characters in the string
								$device_uuid = check_str($row["device_uuid"]);
								$device_key_uuid = check_str($row["device_key_uuid"]);
								$device_key_id = check_str($row["device_key_id"]);
								$device_key_type = check_str($row["device_key_type"]);
								$device_key_line = check_str($row["device_key_line"]);
								$device_key_value = check_str($row["device_key_value"]);
								$device_key_label = check_str($row["device_key_label"]);
								$device_key_category = check_str($row["device_key_category"]);
								$device_key_vendor = check_str($row["device_key_vendor"]);

							//process the profile keys
								if (strlen($row["device_profile_uuid"]) > 0) {
									//get the profile key settings from the array
										foreach ($device_profile_keys as &$field) {
											if ($device_key_uuid == $field["device_key_uuid"]) {
												$database = $field;
												break;
											}
										}
									//determine what to do with the profile key
										if ($device_key_id == $database["device_key_id"]
											&& $device_key_value == $database["device_key_value"]
											&& $device_key_label == $database["device_key_label"]) {
												//profile key unchanged don't save
												$save = false;
										}
										else {
											//profile key has changed remove save the settings to the device
											$device_key_uuid = '';
										}
								}

							//sql add or update
								if (strlen($device_key_uuid) == 0) {
									if (permission_exists('device_key_add') && strlen($device_key_type) > 0 && strlen($device_key_value) > 0) {

										//create the primary keys
											$device_key_uuid = uuid();

										//if the device_uuid is not in the array then get the device_uuid from the database
											if (strlen($device_uuid) == 0) {
												$sql = "SELECT device_uuid, device_profile_uuid FROM v_devices ";
												$sql .= "WHERE device_user_uuid = '".$_SESSION['user_uuid']."' ";
												$prep_statement = $db->prepare(check_sql($sql));
												$prep_statement->execute();
												$row = $prep_statement->fetch(PDO::FETCH_NAMED);
												$device_uuid = $row['device_uuid'];
												unset($row);
											}

										//insert the keys
											$sql = "insert into v_device_keys ";
											$sql .= "(";
											$sql .= "domain_uuid, ";
											$sql .= "device_key_uuid, ";
											$sql .= "device_uuid, ";
											$sql .= "device_key_id, ";
											$sql .= "device_key_type, ";
											$sql .= "device_key_line, ";
											$sql .= "device_key_value, ";
											$sql .= "device_key_label, ";
											$sql .= "device_key_category, ";
											$sql .= "device_key_vendor ";
											$sql .= ") ";
											$sql .= "VALUES (";
											$sql .= "'".$_SESSION['domain_uuid']."', ";
											$sql .= "'".$device_key_uuid."', ";
											$sql .= "'".$device_uuid."', ";
											$sql .= "'".$device_key_id."', ";
											$sql .= "'".$device_key_type."', ";
											$sql .= "'".$device_key_line."', ";
											$sql .= "'".$device_key_value."', ";
											$sql .= "'".$device_key_label."', ";
											$sql .= "'".$device_key_category."', ";
											$sql .= "'".$device_key_vendor."' ";
											$sql .= ");";

										//action add or update
											$action = "add";
									}
								}
								else {
									//action add or update
										$action = "update";

									//update the device keys
										$sql = "update v_device_keys set ";
										if (permission_exists('device_key_id')) {
											$sql .= "device_key_id = '".$device_key_id."', ";
										}
										$sql .= "device_key_type = '".$device_key_type."', ";
										$sql .= "device_key_value = '".$device_key_value."', ";
										$sql .= "device_key_label = '".$device_key_label."' ";
										$sql .= "where domain_uuid = '".$_SESSION['domain_uuid']."' ";
										$sql .= "and device_key_uuid = '".$device_key_uuid."'; ";
								}
								if ($save) {
									$db->exec(check_sql($sql));
									//echo "valid: ".$sql."\n";
								}
								else {
									//echo "invalid: ".$sql."\n";
								}
						}
					}

				//write the provision files
					if (strlen($_SESSION['provision']['path']['text']) > 0) {
						$prov = new provision;
						$prov->domain_uuid = $domain_uuid;
						$response = $prov->write();
					}

				//set the message
					messages::add($text["message-$action"]);

				//redirect the browser
					header("Location: /core/user_settings/user_dashboard.php");
					exit;

			} //if ($_POST["persistformvar"] != "true")
	} //(count($_POST) > 0 && strlen($_POST["persistformvar"]) == 0)

//set the sub array index
	$x = "999";

//get device
	$sql = "SELECT device_uuid, device_profile_uuid FROM v_devices ";
	$sql .= "WHERE device_user_uuid = '".$_SESSION['user_uuid']."' ";
	$prep_statement = $db->prepare(check_sql($sql));
	$prep_statement->execute();
	$row = $prep_statement->fetch(PDO::FETCH_NAMED);
	$device_uuid = $row['device_uuid'];
	$device_profile_uuid = $row['device_profile_uuid'];
	unset($row);

//get device lines
	if (isset($device_uuid)) {
		$sql = "SELECT * from v_device_lines ";
		$sql .= "WHERE device_uuid = '".$device_uuid."' ";
		$prep_statement = $db->prepare(check_sql($sql));
		$prep_statement->execute();
		$device_lines = $prep_statement->fetchAll(PDO::FETCH_NAMED);
	}

//get the user
	if (is_array($device_lines)) {
		foreach ($device_lines as $row) {
			if ($_SESSION['domain_name'] == $row['server_address']) {
				$user_id = $row['user_id'];
				$server_address = $row['server_address'];
				break;
			}
		}
	}

//set the sip profile name
	$sip_profile_name = 'internal';

//get device keys in the right order where device keys are listed after the profile keys
	if (isset($device_uuid)) {
		$sql = "SELECT * FROM v_device_keys ";
		$sql .= "WHERE (";
		$sql .= "device_uuid = '".$device_uuid."' ";
		if (strlen($device_profile_uuid) > 0) {
			$sql .= "or device_profile_uuid = '".$device_profile_uuid."' ";
		}
		$sql .= ") ";
		$sql .= "ORDER BY ";
		$sql .= "device_key_vendor ASC, ";
		$sql .= "CASE device_key_category ";
		$sql .= "WHEN 'line' THEN 1 ";
		$sql .= "WHEN 'memory' THEN 2 ";
		$sql .= "WHEN 'programmable' THEN 3 ";
		$sql .= "WHEN 'expansion' THEN 4 ";
		$sql .= "ELSE 100 END, ";
		if ($db_type == "mysql") {
			$sql .= "device_key_id ASC ";
		}
		else {
			$sql .= "CAST(device_key_id as numeric) ASC, ";
		}
		$sql .= "CASE WHEN device_uuid IS NULL THEN 0 ELSE 1 END ASC ";
		$prep_statement = $db->prepare(check_sql($sql));
		$prep_statement->execute();
		$keys = $prep_statement->fetchAll(PDO::FETCH_NAMED);
		unset($sql,$prep_statement);
	}

//override profile keys with device keys
	if (is_array($device_keys)) {
		foreach($keys as $row) {
			$id = $row['device_key_id'];
			$device_keys[$id] = $row;
			if (is_uuid($row['device_profile_uuid'])) {
				$device_keys[$id]['device_key_owner'] = "profile";
			}
			else {
				$device_keys[$id]['device_key_owner'] = "device";
			}
		}
		unset($keys);
	}

//get the vendor count and last and device information
	if (is_array($device_keys)) {
		$vendor_count = 0;
		foreach($device_keys as $row) {
			if ($previous_vendor != $row['device_key_vendor']) {
				$previous_vendor = $row['device_key_vendor'];
				$device_uuid = $row['device_uuid'];
				$device_key_vendor = $row['device_key_vendor'];
				$device_key_id = $row['device_key_id'];
				$device_key_line = $row['device_key_line'];
				$device_key_category = $row['device_key_category'];
				$vendor_count++;
			}
		}
	}

//add a new key
	if (permission_exists('device_key_add')) {
		$device_keys[$x]['device_key_category'] = $device_key_category;
		$device_keys[$x]['device_key_id'] = '';
		$device_keys[$x]['device_uuid'] = $device_uuid;
		$device_keys[$x]['device_key_vendor'] = $device_key_vendor;
		$device_keys[$x]['device_key_type'] = '';
		$device_keys[$x]['device_key_line'] = '';
		$device_keys[$x]['device_key_value'] = '';
		$device_keys[$x]['device_key_extension'] = '';
		$device_keys[$x]['device_key_label'] = '';
	}

//remove the keys the user is not allowed to edit based on the authorized vendor keys
	if (is_array($device_keys)) {
		foreach($device_keys as $row) {
			//loop through the authorized vendor functions
				$device_key_authorized = false;
				if (is_array($vendor_functions)) {
					foreach($vendor_functions as $function) {
						if (strlen($row['device_key_type'] == 0)) {
							$device_key_authorized = true;
						}
						else {
							if ($function['vendor_name'] == $row['device_key_vendor'] && $function['value'] == $row['device_key_type']) {
								$device_key_authorized = true;
							}
						}
					}
				}
			//unset vendor functions the is not allowed to edit
				if (!$device_key_authorized) {
					unset($device_keys[$row['device_key_id']]);
				}
			//hide protected keys
				if ($row['device_key_protected'] == "true") {
					unset($device_keys[$row['device_key_id']]);
				}
		}
	}

//show the header
	//require_once "resources/header.php";

//show the content
	echo "<form name='frm' id='frm' method='post' action='/app/devices/device_dashboard.php'>\n";

	echo "	<div style='float: left;'>";
	echo "		<b>".$text['title-device_keys']."</b><br />";
	if (!$is_included) {
		echo "	".$text['description-device_keys']."<br />";
	}
	echo "	<br />";
	echo "	</div>\n";

	echo "	<div style='float: right;'>";
	echo "	</div>\n";

	echo "<div style='float: right;'>\n";
	echo "	<input type='button' class='btn' value='".$text['button-apply']."' onclick=\"document.location.href='".PROJECT_PATH."/app/devices/cmd.php?cmd=check_sync&profile=".$sip_profile_name."&user=".$user_id."@".$server_address."&domain=".$server_address."&agent=".$device_key_vendor."';\">&nbsp;\n";
	echo "	<input type='submit' class='btn' value='".$text['button-save']."'>";
	echo "</div>\n";

	if (permission_exists('device_key_edit')) {
		echo "			<table width='100%' border='0' cellpadding='0' cellspacing='0'>\n";
		$x = 0;
		if (is_array($device_keys)) {
			foreach($device_keys as $row) {
				//set the variables
					$device_key_vendor = $row['device_key_vendor'];
					$device_vendor = $row['device_key_vendor'];

				//set the column names
					if ($previous_device_key_vendor != $row['device_key_vendor']) {
						echo "			<tr>\n";
						//echo "				<td class='vtable'>".$text['label-device_key_category']."</td>\n";
						echo "				<th>".$text['label-device_key_id']."</th>\n";
						if (strlen($row['device_key_vendor']) > 0) {
							echo "				<th>".ucwords($row['device_key_vendor'])."</th>\n";
						} else {
							echo "				<th>".$text['label-device_key_type']."</th>\n";
						}
						//echo "				<td class='row_style".$c."'>".$text['label-device_key_line']."</td>\n";
						echo "				<th>".$text['label-device_key_value']."</th>\n";
						//echo "				<td class='row_style".$c."'>".$text['label-device_key_extension']."</td>\n";
						echo "				<th>".$text['label-device_key_label']."</th>\n";
						echo "			</tr>\n";
					}
				//determine whether to hide the element
					if (strlen($device_key_uuid) == 0) {
						$element['hidden'] = false;
						$element['visibility'] = "visibility:visible;";
					}
					else {
						$element['hidden'] = true;
						$element['visibility'] = "visibility:hidden;";
					}
				//add the primary key uuid
					if (strlen($row['device_key_uuid']) > 0) {
						echo "	<input name='device_keys[".$x."][device_key_uuid]' type='hidden' value=\"".$row['device_key_uuid']."\">\n";
	
	
					}

				//show all the rows in the array
					/*
					echo "			<tr>\n";
					echo "<td valign='top' align='left' nowrap='nowrap'>\n";
					echo "	<select class='formfld' name='device_keys[".$x."][device_key_category]'>\n";
					echo "	<option value=''></option>\n";
					if ($row['device_key_category'] == "line") {
						echo "	<option value='line' selected='selected'>".$text['label-line']."</option>\n";
					}
					else {
						echo "	<option value='line'>".$text['label-line']."</option>\n";
					}
					if ($row['device_key_category'] == "memory") {
						echo "	<option value='memory' selected='selected'>".$text['label-memory']."</option>\n";
					}
					else {
						echo "	<option value='memory'>".$text['label-memory']."</option>\n";
					}
					if ($row['device_key_category'] == "programmable") {
						echo "	<option value='programmable' selected='selected'>".$text['label-programmable']."</option>\n";
					}
					else {
						echo "	<option value='programmable'>".$text['label-programmable']."</option>\n";
					}
					if (strlen($device_vendor) == 0) {
						if ($row['device_key_category'] == "expansion") {
							echo "	<option value='expansion' selected='selected'>".$text['label-expansion']."</option>\n";
						}
						else {
							echo "	<option value='expansion'>".$text['label-expansion']."</option>\n";
						}
					}
					else {
						if (strtolower($device_vendor) == "cisco") {
							if ($row['device_key_category'] == "expansion-1" || $row['device_key_category'] == "expansion") {
								echo "	<option value='expansion-1' selected='selected'>".$text['label-expansion']." 1</option>\n";
							}
							else {
								echo "	<option value='expansion-1'>".$text['label-expansion']." 1</option>\n";
							}
							if ($row['device_key_category'] == "expansion-2") {
								echo "	<option value='expansion-2' selected='selected'>".$text['label-expansion']." 2</option>\n";
							}
							else {
								echo "	<option value='expansion-2'>".$text['label-expansion']." 2</option>\n";
							}
						}
						else {
							if ($row['device_key_category'] == "expansion") {
								echo "	<option value='expansion' selected='selected'>".$text['label-expansion']."</option>\n";
							}
							else {
								echo "	<option value='expansion'>".$text['label-expansion']."</option>\n";
							}
						}
	
					}
					echo "	</select>\n";
					echo "</td>\n";
					*/

					echo "<td class='row_style".$c." row_style_slim' valign='top' nowrap='nowrap'>\n";
					if (permission_exists('device_key_id') || permission_exists('device_key_add')) {
						$selected = "selected='selected'";
						echo "	<select class='formfld' name='device_keys[".$x."][device_key_id]'>\n";
						echo "	<option value=''></option>\n";
						$i = 1;
						while ($i < 100) {
							echo "	<option value='$i' ".($row['device_key_id'] == $i ? $selected:"").">$i</option>\n";
							$i++;
						}
						echo "	</select>\n";
					}
					else {
						echo "&nbsp;&nbsp;".$row['device_key_id'];
					}
					echo "</td>\n";

					echo "<td class='row_style".$c." row_style_slim' nowrap='nowrap'>\n";
					//echo "	<input class='formfld' type='text' name='device_keys[".$x."][device_key_type]' style='width: 120px;' maxlength='255' value=\"$row['device_key_type']\">\n";
					?>
					<input class='formfld' type='hidden' id='key_vendor_<?php echo $x; ?>' name='device_keys[<?php echo $x; ?>][device_key_vendor]' value="<?php echo $device_key_vendor; ?>">
					<input class='formfld' type='hidden' id='key_category_<?php echo $x; ?>' name='device_keys[<?php echo $x; ?>][device_key_category]' value="<?php echo $device_key_category; ?>">
					<input class='formfld' type='hidden' id='key_uuid_<?php echo $x; ?>' name='device_keys[<?php echo $x; ?>][device_uuid]' value="<?php echo $device_uuid; ?>">
					<input class='formfld' type='hidden' id='key_key_line_<?php echo $x; ?>' name='device_keys[<?php echo $x; ?>][device_key_line]' value="<?php echo $device_key_line; ?>">
					<?php
					echo "<select class='formfld' name='device_keys[".$x."][device_key_type]' id='key_type_".$x."'>\n";
					echo "	<option value=''></option>\n";
					$previous_vendor = '';
					$i=0;
					if (is_array($vendor_functions)) {
						foreach ($vendor_functions as $function) {
							if (strlen($row['device_key_vendor']) == 0 && $function['vendor_name'] != $previous_vendor) {
								if ($i > 0) { echo "	</optgroup>\n"; }
								echo "	<optgroup label='".ucwords($function['vendor_name'])."'>\n";
							}
							$selected = '';
							if ($row['device_key_vendor'] == $function['vendor_name'] && $row['device_key_type'] == $function['value']) {
								$selected = "selected='selected'";
							}
							if (strlen($row['device_key_vendor']) == 0) {
								echo "		<option value='".$function['value']."' $selected >".$text['label-'.$function['name']]."</option>\n";
							}
							if (strlen($row['device_key_vendor']) > 0 && $row['device_key_vendor'] == $function['vendor_name']) {
								echo "		<option value='".$function['value']."' $selected >".$text['label-'.$function['name']]."</option>\n";
							}
							$previous_vendor = $function['vendor_name'];
							$i++;
						}
					}
					if (strlen($row['device_key_vendor']) == 0) {
						echo "	</optgroup>\n";
					}
					echo "</select>\n";
					echo "</td>\n";
					//echo "<td valign='top' align='left' nowrap='nowrap'>\n";
					//echo "	<select class='formfld' name='device_keys[".$x."][device_key_line]'>\n";
					//echo "		<option value=''></option>\n";
					//for ($l = 0; $l <= 12; $l++) {
						//echo "	<option value='".$l."' ".(($row['device_key_line'] == $l) ? "selected='selected'" : null).">".$l."</option>\n";
					//}
					//echo "	</select>\n";
					//echo "</td>\n";

					echo "<td class='row_style".$c." row_style_slim'>\n";
					echo "	<input class='formfld' style='min-width: 50px; max-width: 100px;' type='text' name='device_keys[".$x."][device_key_value]' maxlength='255' value=\"".$row['device_key_value']."\">\n";
					echo "</td>\n";

					//echo "<td align='left'>\n";
					//echo "	<input class='formfld' type='text' name='device_keys[".$x."][device_key_extension]' style='width: 120px;' maxlength='255' value=\"".$row['device_key_extension']."\">\n";
					//echo "</td>\n";

					echo "<td class='row_style".$c." row_style_slim'>\n";
					echo "	<input class='formfld' style='min-width: 50px; max-width: 100px;' type='text' name='device_keys[".$x."][device_key_label]' maxlength='255' value=\"".$row['device_key_label']."\">\n";
					echo "	<input type='hidden' name='device_keys[".$x."][device_profile_uuid]' value=\"".$row['device_profile_uuid']."\">\n";
					echo "</td>\n";

					//echo "			<td align='left'>\n";
					//echo "				<input type='button' class='btn' value='".$text['button-save']."' onclick='submit_form();'>\n";
					//echo "			</td>\n";
					//echo "				<td nowrap='nowrap'>\n";
					//if (strlen($row['device_key_uuid']) > 0) {
					//	if (permission_exists('device_key_delete')) {
					//		echo "					<a href='device_key_delete.php?device_uuid=".$row['device_uuid']."&id=".$row['device_key_uuid']."' alt='".$text['button-delete']."' onclick=\"return confirm('".$text['confirm-delete']."')\">$v_link_label_delete</a>\n";
					//	}
					//}
					//echo "				</td>\n";

					echo "			</tr>\n";
				//set the previous vendor
					$previous_device_key_vendor = $row['device_key_vendor'];
				//increment the array key
					$x++;
				//alternate the value
					$c = ($c) ? 0 : 1;
			}
		}
		echo "			</table>\n";
		//if (strlen($text['description-keys']) > 0) {
		//	echo "			<br>".$text['description-keys']."\n";
		//}
	}

	echo "</form>";

//show the footer
	//require_once "resources/footer.php";

?>
