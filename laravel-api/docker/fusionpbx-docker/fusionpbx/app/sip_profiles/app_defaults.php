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
*/

//if the number of rows is 0 then read the sip profile xml into the database
	if ($domains_processed == 1) {

		//add the sip profiles to the database
			$sql = "select count(*) as num_rows from v_sip_profiles ";
			$prep_statement = $db->prepare(check_sql($sql));
			if ($prep_statement) {
				$prep_statement->execute();
				$row = $prep_statement->fetch(PDO::FETCH_ASSOC);
				if ($row['num_rows'] == 0) {
					if (file_exists('/usr/share/examples/fusionpbx/resources/templates/conf/sip_profiles')) {
						$sip_profile_dir = '/usr/share/examples/fusionpbx/resources/templates/conf/sip_profiles/*.xml.noload';
					}
					elseif (file_exists('/usr/local/share/fusionpbx/resources/templates/conf/sip_profiles')) {
						$sip_profile_dir = '/usr/local/share/fusionpbx/resources/templates/conf/sip_profiles/*.xml.noload';
					}
					else {
						$sip_profile_dir = $_SERVER["DOCUMENT_ROOT"].PROJECT_PATH.'/resources/templates/conf/sip_profiles/*.xml.noload';
					}
					$db->beginTransaction();
					$xml_files = glob($sip_profile_dir);
					foreach ($xml_files as &$xml_file) {
						//load the sip profile xml and save it into an array
						$sip_profile_xml = file_get_contents($xml_file);
						$xml = simplexml_load_string($sip_profile_xml);
						$json = json_encode($xml);
						$sip_profile = json_decode($json, true);
						$sip_profile_name = $sip_profile['@attributes']['name'];
						$sip_profile_enabled = $sip_profile['@attributes']['enabled'];
						//echo "sip profile name: ".$sip_profile_name."\n";

						if ($sip_profile_name != "{v_sip_profile_name}") {
							//prepare the description
								switch ($sip_profile_name) {
								case "internal":
									$sip_profile_description = "The Internal profile by default requires registration which is used by the endpoints. ";
									$sip_profile_description .= "By default the Internal profile binds to port 5060. ";
									break;
								case "internal-ipv6":
									$sip_profile_description = "The Internal IPV6 profile binds to the IP version 6 address and is similar to the Internal profile.\n";
									break;
								case "external":
									$sip_profile_description = "The External profile external provides anonymous calling in the public context. ";
									$sip_profile_description .= "By default the External profile binds to port 5080. ";
									$sip_profile_description .= "Calls can be sent using a SIP URL \"voip.domain.com:5080\" ";
									break;
								case "external-ipv6":
									$sip_profile_description = "The External IPV6 profile binds to the IP version 6 address and is similar to the External profile.\n";
									break;
								case "lan":
									$sip_profile_description = "The LAN profile is the same as the Internal profile except that it is bound to the LAN IP.\n";
									break;
								default:
									$sip_profile_description = '';
								}

						//add the sip profile if it is not false
							if ($sip_profile_enabled != "false") {
								//insert the sip profile name, description
									$sip_profile_uuid = uuid();
									$sql = "insert into v_sip_profiles";
									$sql .= "(";
									$sql .= "sip_profile_uuid, ";
									$sql .= "sip_profile_name, ";
									$sql .= "sip_profile_description ";
									$sql .= ") ";
									$sql .= "values ";
									$sql .= "( ";
									$sql .= "'".check_str($sip_profile_uuid)."', ";
									$sql .= "'".check_str($sip_profile_name)."', ";
									$sql .= "'".check_str($sip_profile_description)."' ";
									$sql .= ")";
									//echo $sql."\n\n";
									$db->exec(check_sql($sql));
									unset($sql);

								//get the domain, alias and parse values and set as variables
									$sip_profile_domain_name = $sip_profile['domains']['domain']['@attributes']['name'];
									$sip_profile_domain_alias = $sip_profile['domains']['domain']['@attributes']['alias'];
									$sip_profile_domain_parse = $sip_profile['domains']['domain']['@attributes']['parse'];

								//add the sip profile domains name, alias and parse
									$sip_profile_domain_uuid = uuid();
									$sql = "insert into v_sip_profile_domains";
									$sql .= "(";
									$sql .= "sip_profile_domain_uuid, ";
									$sql .= "sip_profile_uuid, ";
									$sql .= "sip_profile_domain_name, ";
									$sql .= "sip_profile_domain_alias, ";
									$sql .= "sip_profile_domain_parse ";
									$sql .= ") ";
									$sql .= "values ";
									$sql .= "( ";
									$sql .= "'".$sip_profile_domain_uuid."', ";
									$sql .= "'".$sip_profile_uuid."', ";
									$sql .= "'".check_str($sip_profile_domain_name)."', ";
									$sql .= "'".check_str($sip_profile_domain_alias)."', ";
									$sql .= "'".check_str($sip_profile_domain_parse)."' ";
									$sql .= ")";
									$db->exec(check_sql($sql));
									unset($sql);

								//add the sip profile settings
									foreach ($sip_profile['settings']['param'] as $row) {
										//get the name and value pair
											$sip_profile_setting_name = $row['@attributes']['name'];
											$sip_profile_setting_value = $row['@attributes']['value'];
											$sip_profile_setting_enabled = $row['@attributes']['enabled'];
											if ($sip_profile_setting_enabled != "false") { $sip_profile_setting_enabled = "true"; }
											//echo "name: $name value: $value\n";
										//add the profile settings into the database
											$sip_profile_setting_uuid = uuid();
											$sql = "insert into v_sip_profile_settings ";
											$sql .= "(";
											$sql .= "sip_profile_setting_uuid, ";
											$sql .= "sip_profile_uuid, ";
											$sql .= "sip_profile_setting_name, ";
											$sql .= "sip_profile_setting_value, ";
											$sql .= "sip_profile_setting_enabled ";
											$sql .= ") ";
											$sql .= "values ";
											$sql .= "( ";
											$sql .= "'".check_str($sip_profile_setting_uuid)."', ";
											$sql .= "'".check_str($sip_profile_uuid)."', ";
											$sql .= "'".check_str($sip_profile_setting_name)."', ";
											$sql .= "'".check_str($sip_profile_setting_value)."', ";
											$sql .= "'".$sip_profile_setting_enabled."' ";
											$sql .= ")";
											//echo $sql."\n\n";
											$db->exec(check_sql($sql));
									}
							}
						}
					}
					$db->commit();

					//save the sip profile xml
					save_sip_profile_xml();

					//apply settings reminder
					$_SESSION["reload_xml"] = true;
				}
				unset($prep_statement);
			}

		//upgrade - add missing sip profiles domain settings
			$sql = "select count(*) as num_rows from v_sip_profile_domains ";
			$prep_statement = $db->prepare(check_sql($sql));
			if ($prep_statement) {
				$prep_statement->execute();
				$row = $prep_statement->fetch(PDO::FETCH_ASSOC);
				if ($row['num_rows'] == 0) {
					if (file_exists('/usr/share/examples/fusionpbx/resources/templates/conf/sip_profiles')) {
						$sip_profile_dir = '/usr/share/examples/fusionpbx/resources/templates/conf/sip_profiles/*.xml.noload';
					}
					elseif (file_exists('/usr/local/share/fusionpbx/resources/templates/conf/sip_profiles')) {
						$sip_profile_dir = '/usr/local/share/fusionpbx/resources/templates/conf/sip_profiles/*.xml.noload';
					}
					else {
						$sip_profile_dir = $_SERVER["DOCUMENT_ROOT"].PROJECT_PATH.'/resources/templates/conf/sip_profiles/*.xml.noload';
					}
					$db->beginTransaction();
					$xml_files = glob($sip_profile_dir);
					foreach ($xml_files as &$xml_file) {
						//load the sip profile xml and save it into an array
							$sip_profile_xml = file_get_contents($xml_file);
							$xml = simplexml_load_string($sip_profile_xml);
							$json = json_encode($xml);
							$sip_profile = json_decode($json, true);
							$sip_profile_name = $sip_profile['@attributes']['name'];
							$sip_profile_enabled = $sip_profile['@attributes']['enabled'];
							//echo "sip profile name: ".$sip_profile_name."\n";

						//get the domain, alias and parse values and set as variables
							$sip_profile_domain_name = $sip_profile['domains']['domain']['@attributes']['name'];
							$sip_profile_domain_alias = $sip_profile['domains']['domain']['@attributes']['alias'];
							$sip_profile_domain_parse = $sip_profile['domains']['domain']['@attributes']['parse'];

						//get the sip_profile_uuid using the sip profile name
							$sql = "select sip_profile_uuid from v_sip_profiles ";
							$sql .= "where sip_profile_name = '$sip_profile_name' ";
							$prep_statement = $db->prepare(check_sql($sql));
							$prep_statement->execute();
							$result = $prep_statement->fetchAll(PDO::FETCH_NAMED);
							$sip_profile_uuid = $result[0]["sip_profile_uuid"];
							unset ($prep_statement);

						//add the sip profile domains name, alias and parse
							if ($sip_profile_uuid) {
								$sip_profile_domain_uuid = uuid();
								$sql = "insert into v_sip_profile_domains";
								$sql .= "(";
								$sql .= "sip_profile_domain_uuid, ";
								$sql .= "sip_profile_uuid, ";
								$sql .= "sip_profile_domain_name, ";
								$sql .= "sip_profile_domain_alias, ";
								$sql .= "sip_profile_domain_parse ";
								$sql .= ") ";
								$sql .= "values ";
								$sql .= "( ";
								$sql .= "'".$sip_profile_domain_uuid."', ";
								$sql .= "'".$sip_profile_uuid."', ";
								$sql .= "'".check_str($sip_profile_domain_name)."', ";
								$sql .= "'".check_str($sip_profile_domain_alias)."', ";
								$sql .= "'".check_str($sip_profile_domain_parse)."' ";
								$sql .= ")";
								$db->exec(check_sql($sql));
								unset($sql);
							}
							
						//unset the sip_profile_uuid
							unset($sip_profile_uuid);
					}
					$db->commit();

					//save the sip profile xml
					save_sip_profile_xml();

					//apply settings reminder
					$_SESSION["reload_xml"] = true;
				}
				unset($prep_statement);
			}
	}

//if empty, set sip_profile_enabled = true
	if ($domains_processed == 1) {
		$sql = "update v_sip_profiles set ";
		$sql .= "sip_profile_enabled = 'true' ";
		$sql .= "where sip_profile_enabled is null ";
		$sql .= "or sip_profile_enabled = '' ";
		$db->exec(check_sql($sql));
		unset($sql);
	}
?>
