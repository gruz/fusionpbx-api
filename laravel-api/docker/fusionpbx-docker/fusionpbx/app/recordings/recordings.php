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
	James Rose <james.o.rose@gmail.com>
*/

//includes
	include "root.php";
	require_once "resources/require.php";
	require_once "resources/check_auth.php";

//add multi-lingual support
	$language = new text;
	$text = $language->get();

//set the max php execution time
	ini_set(max_execution_time,7200);

//get the http get values and set them as php variables
	$order_by = $_GET["order_by"];
	$order = $_GET["order"];

//set the default order
	if ($order_by == '') {
		$order_by = "recording_name";
		$order = "asc";
	}
	
//download the recordings
	if ($_GET['a'] == "download" && (permission_exists('recording_play') || permission_exists('recording_download'))) {
		session_cache_limiter('public');
		if ($_GET['type'] = "rec") {
			//set the path for the directory
				$path = $_SESSION['switch']['recordings']['dir']."/".$_SESSION['domain_name'];

			//if from recordings, get recording details from db
				$recording_uuid = check_str($_GET['id']); //recordings
				if ($recording_uuid != '') {
					$sql = "select recording_filename, recording_base64 from v_recordings ";
					$sql .= "where domain_uuid = '".$domain_uuid."' ";
					$sql .= "and recording_uuid = '".$recording_uuid."' ";
					$prep_statement = $db->prepare(check_sql($sql));
					$prep_statement->execute();
					$result = $prep_statement->fetchAll(PDO::FETCH_ASSOC);
					if (count($result) > 0) {
						foreach($result as &$row) {
							$recording_filename = $row['recording_filename'];
							if ($_SESSION['recordings']['storage_type']['text'] == 'base64' && $row['recording_base64'] != '') {
								$recording_decoded = base64_decode($row['recording_base64']);
								file_put_contents($path.'/'.$recording_filename, $recording_decoded);
							}
							break;
						}
					}
					unset ($sql, $prep_statement, $result, $recording_decoded);
				}

			// build full path
				if(substr($recording_filename,0,1) == '/'){
					$full_recording_path = $path . $recording_filename;
				} else {
					$full_recording_path = $path . '/' . $recording_filename;
				}

			//send the headers and then the data stream
				if (file_exists($full_recording_path)) {
					//content-range
					if (isset($_SERVER['HTTP_RANGE']))  {
						range_download($full_recording_path);
					}
					
					$fd = fopen($full_recording_path, "rb");
					if ($_GET['t'] == "bin") {
						header("Content-Type: application/force-download");
						header("Content-Type: application/octet-stream");
						header("Content-Type: application/download");
						header("Content-Description: File Transfer");
					}
					else {
						$file_ext = substr($recording_filename, -3);
						if ($file_ext == "wav") {
							header("Content-Type: audio/x-wav");
						}
						if ($file_ext == "mp3") {
							header("Content-Type: audio/mpeg");
						}
					}
					header('Content-Disposition: attachment; filename="'.$recording_filename.'"');
					header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
					header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
					// header("Content-Length: " . filesize($full_recording_path));
					ob_clean();
					fpassthru($fd);
				}

			//if base64, remove temp recording file
				if ($_SESSION['recordings']['storage_type']['text'] == 'base64' && $row['recording_base64'] != '') {
					if ($_SESSION['call_recordings']['delete_recording_file']['bool'] != 'false') {
						@unlink($full_recording_path);
					} else {
						rename($full_recording_path, $full_recording_path . ".bak");
					}
				}
		}
		exit;
	}

//upload the recording
	if (permission_exists('recording_upload')) {
		if ($_POST['submit'] == $text['button-upload'] && $_POST['type'] == 'rec' && is_uploaded_file($_FILES['ulfile']['tmp_name'])) {

			//remove special characters
				$recording_filename = str_replace(" ", "_", $_FILES['ulfile']['name']);
				$recording_filename = str_replace("'", "", $recording_filename);

			//make sure the destination directory exists
				if (!is_dir($_SESSION['switch']['recordings']['dir'].'/'.$_SESSION['domain_name'])) {
					event_socket_mkdir($_SESSION['switch']['recordings']['dir'].'/'.$_SESSION['domain_name']);
				}
			
			//move the uploaded files
				move_uploaded_file($_FILES['ulfile']['tmp_name'], $_SESSION['switch']['recordings']['dir'].'/'.$_SESSION['domain_name'].'/'.$recording_filename);

			//set the message
				messages::add($text['message-uploaded'].": ".htmlentities($recording_filename));

			//set the file name to be inserted as the recording description
				$recording_description = base64_encode($_FILES['ulfile']['name']);
				header("Location: recordings.php?rd=".$recording_description);
				exit;
		}
	}

//check the permission
	if (permission_exists('recording_view')) {
		//access granted
	}
	else {
		echo "access denied";
		exit;
	}

//get existing recordings
	$sql = "select recording_uuid, recording_filename, recording_base64 from v_recordings ";
	$sql .= "where domain_uuid = '".$domain_uuid."' ";
	$prep_statement = $db->prepare(check_sql($sql));
	$prep_statement->execute();
	$result = $prep_statement->fetchAll(PDO::FETCH_NAMED);
	foreach ($result as &$row) {
		$array_recordings[$row['recording_uuid']] = $row['recording_filename'];
		$array_base64_exists[$row['recording_uuid']] = ($row['recording_base64'] != '') ? true : false;
		//if not base64, convert back to local files and remove base64 from db
		if ($_SESSION['recordings']['storage_type']['text'] != 'base64' && $row['recording_base64'] != '') {
			if (!file_exists($_SESSION['switch']['recordings']['dir'].'/'.$_SESSION['domain_name'].'/'.$row['recording_filename'])) {
				$recording_decoded = base64_decode($row['recording_base64']);
				file_put_contents($_SESSION['switch']['recordings']['dir'].'/'.$_SESSION['domain_name'].'/'.$row['recording_filename'], $recording_decoded);
				$sql = "update v_recordings set recording_base64 = null where domain_uuid = '".$domain_uuid."' and recording_uuid = '".$row['recording_uuid']."' ";
				$db->exec(check_sql($sql));
				unset($sql);
			}
		}
	}
	unset ($prep_statement);

//add recordings to the database
	if (is_dir($_SESSION['switch']['recordings']['dir'].'/'.$_SESSION['domain_name'].'/')) {
		if ($dh = opendir($_SESSION['switch']['recordings']['dir'].'/'.$_SESSION['domain_name'].'/')) {
			while (($recording_filename = readdir($dh)) !== false) {
				if (filetype($_SESSION['switch']['recordings']['dir']."/".$_SESSION['domain_name']."/".$recording_filename) == "file") {

					if (!in_array($recording_filename, $array_recordings)) {
						//file not found in db, add it
						$recording_uuid = uuid();
						$recording_name = ucwords(str_replace('_', ' ', pathinfo($recording_filename, PATHINFO_FILENAME)));
						$recording_description = check_str(base64_decode($_GET['rd']));
						$sql = "insert into v_recordings ";
						$sql .= "(";
						$sql .= "domain_uuid, ";
						$sql .= "recording_uuid, ";
						$sql .= "recording_filename, ";
						$sql .= "recording_name, ";
						$sql .= "recording_description ";
						if ($_SESSION['recordings']['storage_type']['text'] == 'base64') {
							$sql .= ", recording_base64 ";
						}
						$sql .= ")";
						$sql .= "values ";
						$sql .= "(";
						$sql .= "'".$domain_uuid."', ";
						$sql .= "'".$recording_uuid."', ";
						$sql .= "'".$recording_filename."', ";
						$sql .= "'".$recording_name."', ";
						$sql .= "'".$recording_description."' ";
						if ($_SESSION['recordings']['storage_type']['text'] == 'base64') {
							$recording_base64 = base64_encode(file_get_contents($_SESSION['switch']['recordings']['dir'].'/'.$_SESSION['domain_name'].'/'.$recording_filename));
							$sql .= ", '".$recording_base64."' ";
						}
						$sql .= ")";
						$db->exec(check_sql($sql));
						unset($sql);
					}
					else {
						//file found in db, check if base64 present
						if ($_SESSION['recordings']['storage_type']['text'] == 'base64') {
							$found_recording_uuid = array_search($recording_filename, $array_recordings);
							if (!$array_base64_exists[$found_recording_uuid]) {
								$recording_base64 = base64_encode(file_get_contents($_SESSION['switch']['recordings']['dir'].'/'.$_SESSION['domain_name'].'/'.$recording_filename));
								$sql = "update v_recordings set ";
								$sql .= "recording_base64 = '".$recording_base64."' ";
								$sql .= "where domain_uuid = '".$domain_uuid."' ";
								$sql .= "and recording_uuid = '".$found_recording_uuid."' ";
								$db->exec(check_sql($sql));
								unset($sql);
							}
						}
					}

					//if base64, remove local file
					if ($_SESSION['recordings']['storage_type']['text'] == 'base64' && file_exists($_SESSION['switch']['recordings']['dir'].'/'.$_SESSION['domain_name'].'/'.$recording_filename)) {
						$tmp_full_recording_path = $_SESSION['switch']['recordings']['dir'].'/'.$_SESSION['domain_name'].'/'.$recording_filename;
						if ($_SESSION['call_recordings']['delete_recording_file']['bool'] != 'false') {
							@unlink($tmp_full_recording_path);
						} else {
							rename($tmp_full_recording_path, $tmp_full_recording_path . ".bak");
						}
						unset($tmp_full_recording_path);
					}

				}
			} //while
			closedir($dh);
		} //if
	} //if

//add paging
	require_once "resources/paging.php";

//get total recordings from the database
	$sql = "select count(recording_uuid) as num_rows from v_recordings \n";
	$sql .= "where domain_uuid = '".$_SESSION['domain_uuid']."' ";
	$prep_statement = $db->prepare($sql);
	if ($prep_statement) {
		$prep_statement->execute();
		$row = $prep_statement->fetch(PDO::FETCH_ASSOC);
		$num_rows = $row['num_rows'];
	}
	unset($prep_statement, $row);

//prepare to page the results
	$rows_per_page = ($_SESSION['domain']['paging']['numeric'] != '') ? $_SESSION['domain']['paging']['numeric'] : 50;
	$param = "&order_by=".$order_by."&order=".$order;
	$page = $_GET['page'];
	if (strlen($page) == 0) { $page = 0; $_GET['page'] = 0; }
	list($paging_controls, $rows_per_page, $var_3) = paging($num_rows, $param, $rows_per_page);
	$offset = $rows_per_page * $page;

//get the recordings from the database
	$sql = "select recording_uuid, domain_uuid, recording_filename, recording_name, recording_description from v_recordings ";
	$sql .= "where domain_uuid = '".$domain_uuid."' ";
	$sql .= "order by ".$order_by." ".$order." ";
	$sql .= "limit ".$rows_per_page." offset ".$offset." ";
	$prep_statement = $db->prepare(check_sql($sql));
	$prep_statement->execute();
	$recordings = $prep_statement->fetchAll(PDO::FETCH_NAMED);
	unset ($prep_statement, $sql);

//set alternate row styles
	$c = 0;
	$row_style["0"] = "row_style0";
	$row_style["1"] = "row_style1";

//include the header
	$document['title'] = $text['title'];
	require_once "resources/header.php";

//begin the content
	if (permission_exists('recording_upload')) {
		echo "<table cellpadding='0' cellspacing='0' border='0' align='right'>\n";
		echo "	<tr>\n";
		echo "		<td nowrap='nowrap'>\n";
		echo "			<form action='' method='post' enctype='multipart/form-data' name='frmUpload'>\n";
		echo "			<input name='type' type='hidden' value='rec'>\n";
		echo "			<input name='ulfile' type='file' class='formfld fileinput' style='width: 260px;' id='ulfile'>\n";
		echo "			<input name='submit' type='submit'  class='btn' id='upload' value=\"".$text['button-upload']."\">\n";
		echo "			</form>";
		echo "		</td>\n";
		echo "	</tr>\n";
		echo "</table>";
	}
	echo "<b>".$text['title-recordings']."</b>";
	echo "<br /><br />\n";
	echo $text['description']."\n";
	echo "<br /><br />\n";

	echo "<table class='tr_hover' width='100%' border='0' cellpadding='0' cellspacing='0'>\n";
	echo "<tr>\n";
	echo th_order_by('recording_name', $text['label-recording_name'], $order_by, $order);
	if ($_SESSION['recordings']['storage_type']['text'] != 'base64') {
		echo th_order_by('recording_filename', $text['label-file_name'], $order_by, $order);
	}
	echo "<th class='listhdr' nowrap>".$text['label-tools']."</th>\n";
	if ($_SESSION['recordings']['storage_type']['text'] != 'base64') {
		echo "<th class='listhdr' style='text-align: center;' nowrap>".$text['label-file-size']."</th>\n";
		echo "<th class='listhdr' style='text-align: right;'>".$text['label-uploaded']."</th>\n";
	}
	else {
		echo th_order_by('recording_description', $text['label-description'], $order_by, $order);
	}
	echo "<td class='list_control_icons'>&nbsp;</td>\n";
	echo "</tr>\n";

	//calculate colspan for progress bar
	$colspan = 5; //max
	if ($_SESSION['recordings']['storage_type']['text'] == 'base64') { $colspan = $colspan - 2; }
	if (!(permission_exists('recording_edit') || permission_exists('recording_delete'))) { $colspan = $colspan - 1; }

	if (is_array($recordings)) {
		foreach($recordings as $row) {
			//playback progress bar
			if (permission_exists('recording_play')) {
				echo "<tr id='recording_progress_bar_".escape($row['recording_uuid'])."' style='display: none;'><td class='".$row_style[$c]." playback_progress_bar_background' style='padding: 0; border: none;' colspan='".$colspan."'><span class='playback_progress_bar' id='recording_progress_".escape($row['recording_uuid'])."'></span></td></tr>\n";
			}
			$tr_link = (permission_exists('recording_edit')) ? "href='recording_edit.php?id=".escape($row['recording_uuid'])."'" : null;
			echo "<tr ".$tr_link.">\n";
			echo "	<td valign='top' class='".$row_style[$c]."'>".escape($row['recording_name'])."</td>\n";
			if ($_SESSION['recordings']['storage_type']['text'] != 'base64') {
				echo "	<td valign='top' class='".$row_style[$c]."'>".str_replace('_', '_&#8203;', escape($row['recording_filename']))."</td>\n";
			}
			if (permission_exists('recording_play') || permission_exists('recording_download')) {
				echo "	<td valign='top' class='".$row_style[$c]." row_style_slim tr_link_void' style='width: 55px;'>";
				if (permission_exists('recording_play')) {
					$recording_file_path = $row['recording_filename'];
					$recording_file_name = strtolower(pathinfo($recording_file_path, PATHINFO_BASENAME));
					$recording_file_ext = pathinfo($recording_file_name, PATHINFO_EXTENSION);
					switch ($recording_file_ext) {
						case "wav" : $recording_type = "audio/wav"; break;
						case "mp3" : $recording_type = "audio/mpeg"; break;
						case "ogg" : $recording_type = "audio/ogg"; break;
					}
					echo "<audio id='recording_audio_".escape($row['recording_uuid'])."' style='display: none;' preload='none' ontimeupdate=\"update_progress('".escape($row['recording_uuid'])."')\" onended=\"recording_reset('".escape($row['recording_uuid'])."');\" src=\"".PROJECT_PATH."/app/recordings/recordings.php?a=download&type=rec&id=".escape($row['recording_uuid'])."\" type='".$recording_type."'></audio>";
					echo "<span id='recording_button_".escape($row['recording_uuid'])."' onclick=\"recording_play('".escape($row['recording_uuid'])."')\" title='".$text['label-play']." / ".$text['label-pause']."'>".$v_link_label_play."</span>";
				}
				if (permission_exists('recording_download')) {
					echo "<a href=\"".PROJECT_PATH."/app/recordings/recordings.php?a=download&type=rec&t=bin&id=".escape($row['recording_uuid'])."\" title='".$text['label-download']."'>".$v_link_label_download."</a>";
				}
				echo "	</td>\n";
			}
			if ($_SESSION['recordings']['storage_type']['text'] != 'base64') {
				$file_name = $_SESSION['switch']['recordings']['dir'].'/'.$_SESSION['domain_name'].'/'.$row['recording_filename'];
				if (file_exists($file_name)) {
					$file_size = filesize($file_name);
					$file_size = byte_convert($file_size);
					$file_date = date("M d, Y H:i:s", filemtime($file_name));
				}
				else {
					$file_size = '';
					$file_date = '';
				}
				echo "	<td valign='top' class='".$row_style[$c]."' style='text-align: center; white-space: nowrap;'>".$file_size."</td>\n";
				
				echo "	<td valign='top' class='".$row_style[$c]."' style='text-align: right;'>".$file_date."</td>\n";
			}
			else {
				echo "	<td valign='top' class='row_stylebg' width='30%'>".escape($row['recording_description'])."&nbsp;</td>\n";
			}
			echo "	<td class='list_control_icons'>";
			if (permission_exists('recording_edit')) {
				echo "<a href='recording_edit.php?id=".escape($row['recording_uuid'])."' alt='edit'>$v_link_label_edit</a>";
			}
			if (permission_exists('recording_delete')) {
				echo "<a href='recording_delete.php?id=".escape($row['recording_uuid'])."' alt='delete' onclick=\"return confirm('".$text['confirm-delete']."')\">$v_link_label_delete</a>";
			}
			echo "	</td>\n";
			echo "</tr>\n";

			$c = ($c) ? 0 : 1;
		} //end foreach
		unset($sql, $result, $row_count);
	} //end if results
	echo "</table>\n";
	echo "<br />\n";

	echo "<div align='center'>".$paging_controls."</div>\n";
	echo "<br><br>\n";

//include the footer
	require_once "resources/footer.php";


function range_download($file) {

	$fp = @fopen($file, 'rb');

	$size   = filesize($file); // File size
	$length = $size;           // Content length
	$start  = 0;               // Start byte
	$end    = $size - 1;       // End byte
	// Now that we've gotten so far without errors we send the accept range header
	/* At the moment we only support single ranges.
	 * Multiple ranges requires some more work to ensure it works correctly
	 * and comply with the spesifications: http://www.w3.org/Protocols/rfc2616/rfc2616-sec19.html#sec19.2
	 *
	 * Multirange support annouces itself with:
	 * header('Accept-Ranges: bytes');
	 *
	 * Multirange content must be sent with multipart/byteranges mediatype,
	 * (mediatype = mimetype)
	 * as well as a boundry header to indicate the various chunks of data.
	 */
	header("Accept-Ranges: 0-$length");
	// header('Accept-Ranges: bytes');
	// multipart/byteranges
	// http://www.w3.org/Protocols/rfc2616/rfc2616-sec19.html#sec19.2
	if (isset($_SERVER['HTTP_RANGE'])) {

		$c_start = $start;
		$c_end   = $end;
		// Extract the range string
		list(, $range) = explode('=', $_SERVER['HTTP_RANGE'], 2);
		// Make sure the client hasn't sent us a multibyte range
		if (strpos($range, ',') !== false) {

			// (?) Shoud this be issued here, or should the first
			// range be used? Or should the header be ignored and
			// we output the whole content?
			header('HTTP/1.1 416 Requested Range Not Satisfiable');
			header("Content-Range: bytes $start-$end/$size");
			// (?) Echo some info to the client?
			exit;
		}
		// If the range starts with an '-' we start from the beginning
		// If not, we forward the file pointer
		// And make sure to get the end byte if spesified
		if ($range0 == '-') {

			// The n-number of the last bytes is requested
			$c_start = $size - substr($range, 1);
		}
		else {

			$range  = explode('-', $range);
			$c_start = $range[0];
			$c_end   = (isset($range[1]) && is_numeric($range[1])) ? $range[1] : $size;
		}
		/* Check the range and make sure it's treated according to the specs.
		 * http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html
		 */
		// End bytes can not be larger than $end.
		$c_end = ($c_end > $end) ? $end : $c_end;
		// Validate the requested range and return an error if it's not correct.
		if ($c_start > $c_end || $c_start > $size - 1 || $c_end >= $size) {

			header('HTTP/1.1 416 Requested Range Not Satisfiable');
			header("Content-Range: bytes $start-$end/$size");
			// (?) Echo some info to the client?
			exit;
		}
		$start  = $c_start;
		$end    = $c_end;
		$length = $end - $start + 1; // Calculate new content length
		fseek($fp, $start);
		header('HTTP/1.1 206 Partial Content');
	}
	// Notify the client the byte range we'll be outputting
	header("Content-Range: bytes $start-$end/$size");
	header("Content-Length: $length");

	// Start buffered download
	$buffer = 1024 * 8;
	while(!feof($fp) && ($p = ftell($fp)) <= $end) {

		if ($p + $buffer > $end) {

			// In case we're only outputtin a chunk, make sure we don't
			// read past the length
			$buffer = $end - $p + 1;
		}
		set_time_limit(0); // Reset time limit for big files
		echo fread($fp, $buffer);
		flush(); // Free up memory. Otherwise large files will trigger PHP's memory limit.
	}

	fclose($fp);

}

?>
