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
	Portions created by the Initial Developer are Copyright (C) 2018
	the Initial Developer. All Rights Reserved.

	Contributor(s):
	Mark J Crane <markjcrane@fusionpbx.com>
*/

//if the number of rows is 0 then read the sip profile xml into the database
	if ($domains_processed == 1) {

		//add the sip profiles to the database
			$sql = "select count(*) as num_rows from v_email_templates ";
			$prep_statement = $db->prepare(check_sql($sql));
			if ($prep_statement) {
				$prep_statement->execute();
				$row = $prep_statement->fetch(PDO::FETCH_ASSOC);
				if ($row['num_rows'] == 0) {

					//build the array
					$x = 0;
					$array['email_templates'][$x]['email_template_uuid'] = '5256e0aa-10a3-41a9-a7d9-47240823a186';
					$array['email_templates'][$x]['template_language'] = 'de-at';
					$array['email_templates'][$x]['template_category'] = 'voicemail';
					$array['email_templates'][$x]['template_subcategory'] = 'default';
					$array['email_templates'][$x]['template_subject'] = 'Sprachnachricht von ${caller_id_name} <${caller_id_number}> ${message_duration}';
					$array['email_templates'][$x]['template_body'] .= "<html>\n";
					$array['email_templates'][$x]['template_body'] .= "<body>\n";
					$array['email_templates'][$x]['template_body'] = "Neue Sprachnachricht<br />\n";
					$array['email_templates'][$x]['template_body'] .= "<br />\n";
					$array['email_templates'][$x]['template_body'] .= "Nebenstelle \${voicemail_name_formatted}<br />\n";
					$array['email_templates'][$x]['template_body'] .= "Anrufer <a href=\"tel:\${caller_id_number}\">\${caller_id_number}</a><br />\n";
					$array['email_templates'][$x]['template_body'] .= "Lä nge \${message_duration}<br />\n";
					$array['email_templates'][$x]['template_body'] .= "Nachricht \${message}<br />\n";
					$array['email_templates'][$x]['template_body'] .= "</body>\n";
					$array['email_templates'][$x]['template_body'] .= "</html>\n";
					$array['email_templates'][$x]['template_type'] = 'html';
					$array['email_templates'][$x]['template_enabled'] = 'true';
					$array['email_templates'][$x]['template_description'] = '';
					$x++;
					$array['email_templates'][$x]['email_template_uuid'] = '861e6e04-92fe-4bfb-a983-f29b3a5c07cf';
					$array['email_templates'][$x]['template_language'] = 'de-at';
					$array['email_templates'][$x]['template_category'] = 'voicemail';
					$array['email_templates'][$x]['template_subcategory'] = 'default';
					$array['email_templates'][$x]['template_subject'] = 'Sprachnachricht von ${caller_id_name} <${caller_id_number}> ${message_duration}';
					$array['email_templates'][$x]['template_body'] = "Neue Sprachnachricht\n";
					$array['email_templates'][$x]['template_body'] .= "\n";
					$array['email_templates'][$x]['template_body'] .= "Nebenstelle \${voicemail_name_formatted}\n";
					$array['email_templates'][$x]['template_body'] .= "Anrufer \${caller_id_number}\n";
					$array['email_templates'][$x]['template_body'] .= "Lä nge \${message_duration}\n";
					$array['email_templates'][$x]['template_body'] .= "Nachricht \${message}\n";
					$array['email_templates'][$x]['template_type'] = 'text';
					$array['email_templates'][$x]['template_enabled'] = 'false';
					$array['email_templates'][$x]['template_description'] = '';
					$x++;

					$array['email_templates'][$x]['email_template_uuid'] = 'cb0045f2-6ff1-4ed8-a030-6cec6c65b632';
					$array['email_templates'][$x]['template_language'] = 'de-de';
					$array['email_templates'][$x]['template_category'] = 'voicemail';
					$array['email_templates'][$x]['template_subcategory'] = 'default';
					$array['email_templates'][$x]['template_subject'] = 'Sprachnachricht von ${caller_id_name} <${caller_id_number}> ${message_duration}';
					$array['email_templates'][$x]['template_body'] .= "<html>\n";
					$array['email_templates'][$x]['template_body'] .= "<body>\n";
					$array['email_templates'][$x]['template_body'] = "Neue Sprachnachricht<br />\n";
					$array['email_templates'][$x]['template_body'] .= "<br />\n";
					$array['email_templates'][$x]['template_body'] .= "Nebenstelle \${voicemail_name_formatted}<br />\n";
					$array['email_templates'][$x]['template_body'] .= "Anrufer <a href=\"tel:\${caller_id_number}\">\${caller_id_number}</a><br />\n";
					$array['email_templates'][$x]['template_body'] .= "Lä nge \${message_duration}<br />\n";
					$array['email_templates'][$x]['template_body'] .= "Nachricht \${message}<br />\n";
					$array['email_templates'][$x]['template_body'] .= "</body>\n";
					$array['email_templates'][$x]['template_body'] .= "</html>\n";
					$array['email_templates'][$x]['template_type'] = 'html';
					$array['email_templates'][$x]['template_enabled'] = 'true';
					$array['email_templates'][$x]['template_description'] = '';
					$x++;
					$array['email_templates'][$x]['email_template_uuid'] = 'f45935f0-7dc1-4b92-9bd7-7b35121a3ca7';
					$array['email_templates'][$x]['template_language'] = 'de-de';
					$array['email_templates'][$x]['template_category'] = 'voicemail';
					$array['email_templates'][$x]['template_subcategory'] = 'default';
					$array['email_templates'][$x]['template_subject'] = 'Sprachnachricht von ${caller_id_name} <${caller_id_number}> ${message_duration}';
					$array['email_templates'][$x]['template_body'] = "Neue Sprachnachricht\n";
					$array['email_templates'][$x]['template_body'] .= "\n";
					$array['email_templates'][$x]['template_body'] .= "Nebenstelle \${voicemail_name_formatted}\n";
					$array['email_templates'][$x]['template_body'] .= "Anrufer \${caller_id_number}\n";
					$array['email_templates'][$x]['template_body'] .= "Lä nge \${message_duration}\n";
					$array['email_templates'][$x]['template_body'] .= "Nachricht \${message}\n";
					$array['email_templates'][$x]['template_type'] = 'text';
					$array['email_templates'][$x]['template_enabled'] = 'false';
					$array['email_templates'][$x]['template_description'] = '';
					$x++;

					$array['email_templates'][$x]['email_template_uuid'] = '62d1e7ef-c423-4ac6-be9e-c0e2adbbb60d';
					$array['email_templates'][$x]['template_language'] = 'en-gb';
					$array['email_templates'][$x]['template_category'] = 'voicemail';
					$array['email_templates'][$x]['template_subcategory'] = 'default';
					$array['email_templates'][$x]['template_subject'] = 'Voicemail from ${caller_id_name} <${caller_id_number}> ${message_duration}';
					$array['email_templates'][$x]['template_body'] .= "<html>\n";
					$array['email_templates'][$x]['template_body'] .= "<body>\n";
					$array['email_templates'][$x]['template_body'] .= "From \${caller_id_name} <a href=\"tel:\${caller_id_number}\">\${caller_id_number}</a><br />\n";
					$array['email_templates'][$x]['template_body'] .= "<br />\n";
					$array['email_templates'][$x]['template_body'] .= "To \${voicemail_name_formatted}<br />\n";
					$array['email_templates'][$x]['template_body'] .= "Received \${message_date}<br />\n";
					$array['email_templates'][$x]['template_body'] .= "Length \${message_duration}<br />\n";
					$array['email_templates'][$x]['template_body'] .= "Message \${message}<br />\n";
					$array['email_templates'][$x]['template_body'] .= "</body>\n";
					$array['email_templates'][$x]['template_body'] .= "</html>\n";
					$array['email_templates'][$x]['template_type'] = 'html';
					$array['email_templates'][$x]['template_enabled'] = 'true';
					$array['email_templates'][$x]['template_description'] = '';
					$x++;
					$array['email_templates'][$x]['email_template_uuid'] = 'defb880a-e368-4862-b946-a5244871af55';
					$array['email_templates'][$x]['template_language'] = 'en-gb';
					$array['email_templates'][$x]['template_category'] = 'voicemail';
					$array['email_templates'][$x]['template_subcategory'] = 'default';
					$array['email_templates'][$x]['template_subject'] = 'Voicemail from ${caller_id_name} <${caller_id_number}> ${message_duration}';
					$array['email_templates'][$x]['template_body'] = "Voicemail from \${caller_id_name} <\${caller_id_number}>\n";
					$array['email_templates'][$x]['template_body'] .= "\n";
					$array['email_templates'][$x]['template_body'] .= "To \${voicemail_name_formatted}\n";
					$array['email_templates'][$x]['template_body'] .= "Received \${message_date}\n";
					$array['email_templates'][$x]['template_body'] .= "Length \${message_duration}\n";
					$array['email_templates'][$x]['template_body'] .= "Message \${message}\n";
					$array['email_templates'][$x]['template_type'] = 'text';
					$array['email_templates'][$x]['template_enabled'] = 'false';
					$array['email_templates'][$x]['template_description'] = '';
					$x++;

					$array['email_templates'][$x]['email_template_uuid'] = '5d73fb7f-c48a-4752-b5e9-bfe94b4b02d6';
					$array['email_templates'][$x]['template_language'] = 'en-us';
					$array['email_templates'][$x]['template_category'] = 'voicemail';
					$array['email_templates'][$x]['template_subcategory'] = 'transcription';
					$array['email_templates'][$x]['template_subject'] = 'Voicemail from ${caller_id_name} <${caller_id_number}> ${message_duration}';
					$array['email_templates'][$x]['template_body'] .= "<html>\n";
					$array['email_templates'][$x]['template_body'] .= "<body>\n";
					$array['email_templates'][$x]['template_body'] .= "Voicemail from \${caller_id_name} <a href=\"tel:\${caller_id_number}\">\${caller_id_number}</a><br />\n";
					$array['email_templates'][$x]['template_body'] .= "<br />\n";
					$array['email_templates'][$x]['template_body'] .= "To \${voicemail_name_formatted}<br />\n";
					$array['email_templates'][$x]['template_body'] .= "Received \${message_date}<br />\n";
					$array['email_templates'][$x]['template_body'] .= "Length \${message_duration}<br />\n";
					$array['email_templates'][$x]['template_body'] .= "Message \${message}<br />\n";
					$array['email_templates'][$x]['template_body'] .= "<br />\n";
					$array['email_templates'][$x]['template_body'] .= "Transcription<br />\n";
					$array['email_templates'][$x]['template_body'] .= "\${message_text}\n";
					$array['email_templates'][$x]['template_body'] .= "</body>\n";
					$array['email_templates'][$x]['template_body'] .= "</html>\n";
					$array['email_templates'][$x]['template_type'] = 'html';
					$array['email_templates'][$x]['template_enabled'] = 'true';
					$array['email_templates'][$x]['template_description'] = '';
					$x++;
					$array['email_templates'][$x]['email_template_uuid'] = 'c5f3ae42-a5af-4bb7-80a3-480cfe90fb49';
					$array['email_templates'][$x]['template_language'] = 'en-gb';
					$array['email_templates'][$x]['template_category'] = 'voicemail';
					$array['email_templates'][$x]['template_subcategory'] = 'transcription';
					$array['email_templates'][$x]['template_subject'] = 'Voicemail from ${caller_id_name} <${caller_id_number}> ${message_duration}';
					$array['email_templates'][$x]['template_body'] = "Voicemail from \${caller_id_name} <\${caller_id_number}>\n";
					$array['email_templates'][$x]['template_body'] .= "\n";
					$array['email_templates'][$x]['template_body'] .= "To \${voicemail_name_formatted}\n";
					$array['email_templates'][$x]['template_body'] .= "Received \${message_date}\n";
					$array['email_templates'][$x]['template_body'] .= "Length \${message_duration}\n";
					$array['email_templates'][$x]['template_body'] .= "Message \${message}\n";
					$array['email_templates'][$x]['template_body'] .= "\n";
					$array['email_templates'][$x]['template_body'] .= "Transcription\n";
					$array['email_templates'][$x]['template_body'] .= "\${message_text}\n";
					$array['email_templates'][$x]['template_type'] = 'text';
					$array['email_templates'][$x]['template_enabled'] = 'false';
					$array['email_templates'][$x]['template_description'] = '';
					$x++;

					$array['email_templates'][$x]['email_template_uuid'] = 'fbd0c8ea-6adb-4f8b-92cf-00e9087e3568';
					$array['email_templates'][$x]['template_language'] = 'en-us';
					$array['email_templates'][$x]['template_category'] = 'voicemail';
					$array['email_templates'][$x]['template_subcategory'] = 'default';
					$array['email_templates'][$x]['template_subject'] = 'Voicemail from ${caller_id_name} <${caller_id_number}> ${message_duration}';
					$array['email_templates'][$x]['template_body'] .= "<html>\n";
					$array['email_templates'][$x]['template_body'] .= "<body>\n";
					$array['email_templates'][$x]['template_body'] .= "Voicemail from \${caller_id_name} <a href=\"tel:\${caller_id_number}\">\${caller_id_number}</a><br />\n";
					$array['email_templates'][$x]['template_body'] .= "<br />\n";
					$array['email_templates'][$x]['template_body'] .= "To \${voicemail_name_formatted}<br />\n";
					$array['email_templates'][$x]['template_body'] .= "Received \${message_date}<br />\n";
					$array['email_templates'][$x]['template_body'] .= "Length \${message_duration}<br />\n";
					$array['email_templates'][$x]['template_body'] .= "Message \${message}<br />\n";
					$array['email_templates'][$x]['template_body'] .= "</body>\n";
					$array['email_templates'][$x]['template_body'] .= "</html>\n";
					$array['email_templates'][$x]['template_type'] = 'html';
					$array['email_templates'][$x]['template_enabled'] = 'true';
					$array['email_templates'][$x]['template_description'] = '';
					$x++;
					$array['email_templates'][$x]['email_template_uuid'] = '56bb3416-53fc-4a3d-936d-9e3ba869081d';
					$array['email_templates'][$x]['template_language'] = 'en-us';
					$array['email_templates'][$x]['template_category'] = 'voicemail';
					$array['email_templates'][$x]['template_subcategory'] = 'default';
					$array['email_templates'][$x]['template_subject'] = 'Voicemail from ${caller_id_name} <${caller_id_number}> ${message_duration}';
					$array['email_templates'][$x]['template_body'] = "Voicemail from \${caller_id_name} <\${caller_id_number}>\n";
					$array['email_templates'][$x]['template_body'] .= "\n";
					$array['email_templates'][$x]['template_body'] .= "To \${voicemail_name_formatted}\n";
					$array['email_templates'][$x]['template_body'] .= "Received \${message_date}\n";
					$array['email_templates'][$x]['template_body'] .= "Length \${message_duration}\n";
					$array['email_templates'][$x]['template_body'] .= "Message \${message}\n";
					$array['email_templates'][$x]['template_type'] = 'text';
					$array['email_templates'][$x]['template_enabled'] = 'false';
					$array['email_templates'][$x]['template_description'] = '';
					$x++;

					$array['email_templates'][$x]['email_template_uuid'] = '233135c9-7e3e-48d6-b6ad-ba1a383c0ac4';
					$array['email_templates'][$x]['template_language'] = 'en-us';
					$array['email_templates'][$x]['template_category'] = 'voicemail';
					$array['email_templates'][$x]['template_subcategory'] = 'transcription';
					$array['email_templates'][$x]['template_subject'] = 'Voicemail from ${caller_id_name} <${caller_id_number}> ${message_duration}';
					$array['email_templates'][$x]['template_body'] .= "<html>\n";
					$array['email_templates'][$x]['template_body'] .= "<body>\n";
					$array['email_templates'][$x]['template_body'] .= "Voicemail from \${caller_id_name} <a href=\"tel:\${caller_id_number}\">\${caller_id_number}</a><br />\n";
					$array['email_templates'][$x]['template_body'] .= "<br />\n";
					$array['email_templates'][$x]['template_body'] .= "To \${voicemail_name_formatted}<br />\n";
					$array['email_templates'][$x]['template_body'] .= "Received \${message_date}<br />\n";
					$array['email_templates'][$x]['template_body'] .= "Length \${message_duration}<br />\n";
					$array['email_templates'][$x]['template_body'] .= "Message \${message}<br />\n";
					$array['email_templates'][$x]['template_body'] .= "<br />\n";
					$array['email_templates'][$x]['template_body'] .= "Transcription<br />\n";
					$array['email_templates'][$x]['template_body'] .= "\${message_text}\n";
					$array['email_templates'][$x]['template_body'] .= "</body>\n";
					$array['email_templates'][$x]['template_body'] .= "</html>\n";
					$array['email_templates'][$x]['template_type'] = 'html';
					$array['email_templates'][$x]['template_enabled'] = 'true';
					$array['email_templates'][$x]['template_description'] = '';
					$x++;
					$array['email_templates'][$x]['email_template_uuid'] = 'c8f14f37-4998-41a2-9c7b-7e810c77c570';
					$array['email_templates'][$x]['template_language'] = 'en-us';
					$array['email_templates'][$x]['template_category'] = 'voicemail';
					$array['email_templates'][$x]['template_subcategory'] = 'transcription';
					$array['email_templates'][$x]['template_subject'] = 'Voicemail from ${caller_id_name} <${caller_id_number}> ${message_duration}';
					$array['email_templates'][$x]['template_body'] = "Voicemail from \${caller_id_name} <\${caller_id_number}>\n";
					$array['email_templates'][$x]['template_body'] .= "\n";
					$array['email_templates'][$x]['template_body'] .= "To \${voicemail_name_formatted}\n";
					$array['email_templates'][$x]['template_body'] .= "Received \${message_date}\n";
					$array['email_templates'][$x]['template_body'] .= "Length \${message_duration}\n";
					$array['email_templates'][$x]['template_body'] .= "Message \${message}\n";
					$array['email_templates'][$x]['template_body'] .= "\n";
					$array['email_templates'][$x]['template_body'] .= "Transcription\n";
					$array['email_templates'][$x]['template_body'] .= "\${message_text}\n";
					$array['email_templates'][$x]['template_type'] = 'text';
					$array['email_templates'][$x]['template_enabled'] = 'false';
					$array['email_templates'][$x]['template_description'] = '';
					$x++;

					$array['email_templates'][$x]['email_template_uuid'] = '133860ce-175f-4a6f-bfa3-ef7322e80b98';
					$array['email_templates'][$x]['template_language'] = 'en-us';
					$array['email_templates'][$x]['template_category'] = 'missed';
					$array['email_templates'][$x]['template_subcategory'] = 'default';
					$array['email_templates'][$x]['template_subject'] = 'Missed Call from ${caller_id_name} <${caller_id_number}>';
					$array['email_templates'][$x]['template_body'] .= "<html>\n";
					$array['email_templates'][$x]['template_body'] .= "<body>\n";
					$array['email_templates'][$x]['template_body'] = "Missed Call from \${caller_id_name} &lt;<a href=\"tel:\${caller_id_number}\">\${caller_id_number}</a>&gt; to \${sip_to_user} ext \${dialed_user}\n";
					$array['email_templates'][$x]['template_body'] .= "</body>\n";
					$array['email_templates'][$x]['template_body'] .= "</html>\n";				
					$array['email_templates'][$x]['template_type'] = 'html';
					$array['email_templates'][$x]['template_enabled'] = 'true';
					$array['email_templates'][$x]['template_description'] = '';
					$x++;
					$array['email_templates'][$x]['email_template_uuid'] = '890626c4-907b-44ad-9cf6-02d0b0a2379d';
					$array['email_templates'][$x]['template_language'] = 'en-us';
					$array['email_templates'][$x]['template_category'] = 'missed';
					$array['email_templates'][$x]['template_subcategory'] = 'default';
					$array['email_templates'][$x]['template_subject'] = 'Missed Call from ${caller_id_name} <${caller_id_number}>';
					$array['email_templates'][$x]['template_body'] = "Missed Call from \${caller_id_name} &lt;\${caller_id_number}&gt; to \${sip_to_user} ext \${dialed_user}\n";
					$array['email_templates'][$x]['template_type'] = 'text';
					$array['email_templates'][$x]['template_enabled'] = 'false';
					$array['email_templates'][$x]['template_description'] = '';
					$x++;

					$array['email_templates'][$x]['email_template_uuid'] = 'eafaf4fe-b21d-47a0-ab2c-5943cb8cb5be';
					$array['email_templates'][$x]['template_language'] = 'en-gb';
					$array['email_templates'][$x]['template_category'] = 'missed';
					$array['email_templates'][$x]['template_subcategory'] = 'default';
					$array['email_templates'][$x]['template_subject'] = 'Missed Call from ${caller_id_name} <${caller_id_number}>';
					$array['email_templates'][$x]['template_body'] .= "<html>\n";
					$array['email_templates'][$x]['template_body'] .= "<body>\n";
					$array['email_templates'][$x]['template_body'] .= "Missed Call from \${caller_id_name} &lt;<a href=\"tel:\${caller_id_number}\">\${caller_id_number}</a>&gt; to \${sip_to_user} ext \${dialed_user}\n";
					$array['email_templates'][$x]['template_body'] .= "</body>\n";
					$array['email_templates'][$x]['template_body'] .= "</html>\n";
					$array['email_templates'][$x]['template_type'] = 'html';
					$array['email_templates'][$x]['template_enabled'] = 'true';
					$array['email_templates'][$x]['template_description'] = '';
					$x++;
					$array['email_templates'][$x]['email_template_uuid'] = 'a1b11ded-831f-4b81-8a23-fce866196508';
					$array['email_templates'][$x]['template_language'] = 'en-gb';
					$array['email_templates'][$x]['template_category'] = 'missed';
					$array['email_templates'][$x]['template_subcategory'] = 'default';
					$array['email_templates'][$x]['template_subject'] = 'Missed Call from ${caller_id_name} <${caller_id_number}>';
					$array['email_templates'][$x]['template_body'] .= "Missed Call from \${caller_id_name} &lt;\${caller_id_number}&gt; to \${sip_to_user} ext \${dialed_user}\n";
					$array['email_templates'][$x]['template_type'] = 'text';
					$array['email_templates'][$x]['template_enabled'] = 'false';
					$array['email_templates'][$x]['template_description'] = '';
					$x++;

					//add the temporary permission
					$p = new permissions;
					$p->add("email_template_add", 'temp');
					$p->add("email_template_edit", 'temp');

					//save to the data
					$database = new database;
					$database->app_name = 'email_templates';
					$database->app_uuid = '8173e738-2523-46d5-8943-13883befd2fd';
					$database->save($array);
					//$message = $database->message;
					unset($array);
					
					//remove the temporary permission
					$p->delete("email_template_add", 'temp');
					$p->delete("email_template_edit", 'temp');

				} //if ($row['num_rows'] == 0)
			} //if ($prep_statement)
	} //if ($domains_processed == 1)

?>
