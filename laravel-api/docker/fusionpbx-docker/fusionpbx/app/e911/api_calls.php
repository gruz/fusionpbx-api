<?php

require_once "app/e911/functions.php";


function validate_e911_data($address_data) {

	$e911_api_user = $_SESSION['e911']['api_user']['text'];
	$e911_api_secret = $_SESSION['e911']['api_secret']['text'];

	$check_flag = isset($address_data['e911_city'])?True:False;
	if (!$check_flag || $check_flag == '') {
		return False;
	}

	$xml_soap_request =  "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
	$xml_soap_request .= "<soap:Envelope xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\" xmlns:soap=\"http://schemas.xmlsoap.org/soap/envelope/\">\n";
	$xml_soap_request .= "  <soap:Body>\n";
	$xml_soap_request .= "    <validate911 xmlns=\"http://tempuri.org/\">\n";
	$xml_soap_request .= "      <login>".$e911_api_user."</login>\n";
	$xml_soap_request .= "      <secret>".$e911_api_secret."</secret>\n";
	$xml_soap_request .= "      <address1>".$address_data['e911_address_1']."</address1>\n";
	$xml_soap_request .= "      <address2>".$address_data['e911_address_2']."</address2>\n";
	$xml_soap_request .= "      <city>".$address_data['e911_city']."</city>\n";
	$xml_soap_request .= "      <state>".$address_data['e911_state']."</state>\n";
	$xml_soap_request .= "      <zip>".$address_data['e911_zip']."</zip>\n";
	$xml_soap_request .= "      <plusFour>".$address_data['e911_zip_4']."</plusFour>\n";
	$xml_soap_request .= "      <callerName>".$address_data['e911_callername']."</callerName>\n";
	$xml_soap_request .= "    </validate911>\n";
	$xml_soap_request .= "  </soap:Body>\n";
	$xml_soap_request .= "</soap:Envelope>";

	$response = send_911_api($xml_soap_request);

	if ($response['soap:Body']['validate911Response']['validate911Result']['responseCode'] == '100') {
		return True;
	}
	return False;
}

function add_e911_data($e911_data) {
	$e911_api_user = $_SESSION['e911']['api_user']['text'];
	$e911_api_secret = $_SESSION['e911']['api_secret']['text'];

	$e911_did_length = isset($_SESSION['e911']['did_length']['text'])?$_SESSION['e911']['did_length']['text']:10;
	$did_corrected = substr($e911_data['e911_did'], -$e911_did_length);

	$xml_soap_request =  "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
	$xml_soap_request .= "<soap:Envelope xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\" xmlns:soap=\"http://schemas.xmlsoap.org/soap/envelope/\">\n";
	$xml_soap_request .= "  <soap:Body>\n";
	$xml_soap_request .= "    <insert911 xmlns=\"http://tempuri.org/\">\n";
	$xml_soap_request .= "      <login>".$e911_api_user."</login>\n";
	$xml_soap_request .= "      <secret>".$e911_api_secret."</secret>\n";
	$xml_soap_request .= "      <did>".$did_corrected."</did>\n";
	$xml_soap_request .= "      <address1>".$e911_data['e911_address_1']."</address1>\n";
	$xml_soap_request .= "      <address2>".$e911_data['e911_address_2']."</address2>\n";
	$xml_soap_request .= "      <city>".$e911_data['e911_city']."</city>\n";
	$xml_soap_request .= "      <state>".$e911_data['e911_state']."</state>\n";
	$xml_soap_request .= "      <zip>".$e911_data['e911_zip']."</zip>\n";
	$xml_soap_request .= "      <plusFour>".$e911_data['e911_zip_4']."</plusFour>\n";
	$xml_soap_request .= "      <callerName>".$e911_data['e911_callername']."</callerName>\n";
	$xml_soap_request .= "    </insert911>\n";
	$xml_soap_request .= "  </soap:Body>\n";
	$xml_soap_request .= "</soap:Envelope>";

	$response = send_911_api($xml_soap_request);

	$response_code = isset($response['soap:Body']['insert911Response']['insert911Result']['responseCode'])?$response['soap:Body']['insert911Response']['insert911Result']['responseCode']:False;

	if ($response_code != '100') {
		return False;
	}

	return True;
}

function update_e911_data($e911_data) {

	$e911_api_user = $_SESSION['e911']['api_user']['text'];
	$e911_api_secret = $_SESSION['e911']['api_secret']['text'];

	$e911_did_length = isset($_SESSION['e911']['did_length']['text'])?$_SESSION['e911']['did_length']['text']:10;
	$did_corrected = substr($e911_data['e911_did'], -$e911_did_length);

	$xml_soap_request =  "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
	$xml_soap_request .= "<soap:Envelope xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\" xmlns:soap=\"http://schemas.xmlsoap.org/soap/envelope/\">\n";
	$xml_soap_request .= "  <soap:Body>\n";
	$xml_soap_request .= "    <update911 xmlns=\"http://tempuri.org/\">\n";
	$xml_soap_request .= "      <login>".$e911_api_user."</login>\n";
	$xml_soap_request .= "      <secret>".$e911_api_secret."</secret>\n";
	$xml_soap_request .= "      <did>".$did_corrected."</did>\n";
	$xml_soap_request .= "      <address1>".$e911_data['e911_address_1']."</address1>\n";
	$xml_soap_request .= "      <address2>".$e911_data['e911_address_2']."</address2>\n";
	$xml_soap_request .= "      <city>".$e911_data['e911_city']."</city>\n";
	$xml_soap_request .= "      <state>".$e911_data['e911_state']."</state>\n";
	$xml_soap_request .= "      <zip>".$e911_data['e911_zip']."</zip>\n";
	$xml_soap_request .= "      <plusFour>".$e911_data['e911_zip_4']."</plusFour>\n";
	$xml_soap_request .= "      <callerName>".$e911_data['e911_callername']."</callerName>\n";
	$xml_soap_request .= "    </update911>\n";
	$xml_soap_request .= "  </soap:Body>\n";
	$xml_soap_request .= "</soap:Envelope>";

	$response = send_911_api($xml_soap_request);

	$response_code = isset($response['soap:Body']['update911Response']['update911Result']['responseCode'])?$response['soap:Body']['update911Response']['update911Result']['responseCode']:False;

	if ($response_code != '100') {
		return False;
	}

	return True;
}

function query_e911_data($did) {
	$e911_api_user = $_SESSION['e911']['api_user']['text'];
	$e911_api_secret = $_SESSION['e911']['api_secret']['text'];
	$e911_did_length = isset($_SESSION['e911']['did_length']['text'])?$_SESSION['e911']['did_length']['text']:10;

	$did_corrected = substr($did, -$e911_did_length);

	$xml_soap_request =  "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
	$xml_soap_request .= "<soap:Envelope xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\" xmlns:soap=\"http://schemas.xmlsoap.org/soap/envelope/\">\n";
	$xml_soap_request .= "  <soap:Body>\n";
	$xml_soap_request .= "    <query911 xmlns=\"http://tempuri.org/\">\n";
	$xml_soap_request .= "      <login>".$e911_api_user."</login>\n";
	$xml_soap_request .= "      <secret>".$e911_api_secret."</secret>\n";
	$xml_soap_request .= "      <did>".$did_corrected."</did>\n";
	$xml_soap_request .= "    </query911>\n";
	$xml_soap_request .= "  </soap:Body>\n";
	$xml_soap_request .= "</soap:Envelope>";

	$response = send_911_api($xml_soap_request);

	$response_code = isset($response['soap:Body']['query911Response']['query911Result']['responseCode'])?$response['soap:Body']['query911Response']['query911Result']['responseCode']:False;

	if ($response_code != '100') {
		return False;
	}

	// Seems answer was changed.
	$response_data = isset($response['soap:Body']['query911Response']['query911Result']['DID911s']['DID911'])?$response['soap:Body']['query911Response']['query911Result']['DID911s']['DID911']:False;
	// Try old path as well
	if (!$response_data) {
		$response_data = isset($response['soap:Body']['query911Response']['query911Result']['VILocations']['VILocation'])?$response['soap:Body']['query911Response']['query911Result']['VILocations']['VILocation']:False;
	}

	// We cannot parse this!
	if (!$response_data) {
		return False;
	}

	$result = array();
	$result['e911_address_1'] = isset($response_data['address1'])?$response_data['address1']:"";
	$result['e911_address_2'] = isset($response_data['address2'])?$response_data['address2']:"";
	$result['e911_city'] = isset($response_data['city'])?$response_data['city']:"";
	$result['e911_state'] = isset($response_data['state'])?$response_data['state']:"";
	$result['e911_zip'] = isset($response_data['zipCode'])?$response_data['zipCode']:"";
	$result['e911_zip_4'] = isset($response_data['plusFour'])?$response_data['plusFour']:"";
	$result['e911_callername'] = isset($response_data['callerName'])?$response_data['callerName']:"";
	$result['e911_validated'] = isset($response_data['statusCode'])?$response_data['statusCode']:"Not Validated";

	return $result;
}

function remove_e911_data($did) {
	$e911_api_user = $_SESSION['e911']['api_user']['text'];
	$e911_api_secret = $_SESSION['e911']['api_secret']['text'];

	$e911_did_length = isset($_SESSION['e911']['did_length']['text'])?$_SESSION['e911']['did_length']['text']:10;
	$did_corrected = substr($did, -$e911_did_length);

	$xml_soap_request =  "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
	$xml_soap_request .= "<soap:Envelope xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\" xmlns:soap=\"http://schemas.xmlsoap.org/soap/envelope/\">\n";
	$xml_soap_request .= "  <soap:Body>\n";
	$xml_soap_request .= "    <remove911 xmlns=\"http://tempuri.org/\">\n";
	$xml_soap_request .= "      <login>".$e911_api_user."</login>\n";
	$xml_soap_request .= "      <secret>".$e911_api_secret."</secret>\n";
	$xml_soap_request .= "      <did>".$did_corrected."</did>\n";
	$xml_soap_request .= "    </remove911>\n";
	$xml_soap_request .= "  </soap:Body>\n";
	$xml_soap_request .= "</soap:Envelope>";

	$response = send_911_api($xml_soap_request);

	$response_code = isset($response['soap:Body']['remove911Response']['remove911Result']['responseCode'])?$response['soap:Body']['remove911Response']['remove911Result']['responseCode']:False;

	if ($response_code != '100') {
		return False;
	}
	return True;
}

function add_e911_alert($did, $email) {

	if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
		return False;
	}

	$e911_api_user = $_SESSION['e911']['api_user']['text'];
	$e911_api_secret = $_SESSION['e911']['api_secret']['text'];
	$e911_did_length = isset($_SESSION['e911']['did_length']['text'])?$_SESSION['e911']['did_length']['text']:10;

	$did_corrected = substr($did, -$e911_did_length);

	$xml_soap_request =  "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
	$xml_soap_request .= "<soap:Envelope xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\" xmlns:soap=\"http://schemas.xmlsoap.org/soap/envelope/\">\n";
	$xml_soap_request .= "  <soap:Body>\n";
	$xml_soap_request .= "    <Add911Alert xmlns=\"http://tempuri.org/\">\n";
	$xml_soap_request .= "      <login>".$e911_api_user."</login>\n";
	$xml_soap_request .= "      <secret>".$e911_api_secret."</secret>\n";
	$xml_soap_request .= "      <tn>".$did_corrected."</tn>\n";
	$xml_soap_request .= "      <email>".$email."</email>\n";
	$xml_soap_request .= "    </Add911Alert>\n";
	$xml_soap_request .= "  </soap:Body>\n";
	$xml_soap_request .= "</soap:Envelope>";

	$response = send_911_api($xml_soap_request);

	$response_code = isset($response['soap:Body']['Add911AlertResponse']['Add911AlertResult']['responseCode'])?$response['soap:Body']['Add911AlertResponse']['Add911AlertResult']['responseCode']:False;

	if ($response_code != '100') {
		return False;
	}
	return True;
}

function remove_e911_alert($did) {
	$e911_api_user = $_SESSION['e911']['api_user']['text'];
	$e911_api_secret = $_SESSION['e911']['api_secret']['text'];
	$e911_did_length = isset($_SESSION['e911']['did_length']['text'])?$_SESSION['e911']['did_length']['text']:10;

	$did_corrected = substr($did, -$e911_did_length);

	$email = query_e911_alert($did_corrected);
	if ($email) {
		$xml_soap_request =  "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
		$xml_soap_request .= "<soap:Envelope xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\" xmlns:soap=\"http://schemas.xmlsoap.org/soap/envelope/\">\n";
		$xml_soap_request .= "  <soap:Body>\n";
		$xml_soap_request .= "    <Remove911Alert xmlns=\"http://tempuri.org/\">\n";
		$xml_soap_request .= "      <login>".$e911_api_user."</login>\n";
		$xml_soap_request .= "      <secret>".$e911_api_secret."</secret>\n";
		$xml_soap_request .= "      <tn>".$did_corrected."</tn>\n";
		$xml_soap_request .= "      <email>".$email."</email>\n";
		$xml_soap_request .= "    </Remove911Alert>\n";
		$xml_soap_request .= "  </soap:Body>\n";
		$xml_soap_request .= "</soap:Envelope>";

		$response = send_911_api($xml_soap_request);

		$response_code = isset($response['soap:Body']['Remove911AlertResponse']['Remove911AlertResult']['responseCode'])?$response['soap:Body']['Remove911AlertResponse']['Remove911AlertResult']['responseCode']:False;

		if ($response_code == '100') {
			return True;
		}
	}
	return False;
}

function query_e911_alert($did) {
	$e911_api_user = $_SESSION['e911']['api_user']['text'];
	$e911_api_secret = $_SESSION['e911']['api_secret']['text'];
	$e911_did_length = isset($_SESSION['e911']['did_length']['text'])?$_SESSION['e911']['did_length']['text']:10;

	$did_corrected = substr($did, -$e911_did_length);

	$xml_soap_request =  "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
	$xml_soap_request .= "<soap:Envelope xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\" xmlns:soap=\"http://schemas.xmlsoap.org/soap/envelope/\">\n";
	$xml_soap_request .= "  <soap:Body>\n";
	$xml_soap_request .= "    <Query911Alert xmlns=\"http://tempuri.org/\">\n";
	$xml_soap_request .= "      <login>".$e911_api_user."</login>\n";
	$xml_soap_request .= "      <secret>".$e911_api_secret."</secret>\n";
	$xml_soap_request .= "      <tn>".$did_corrected."</tn>\n";
	$xml_soap_request .= "    </Query911Alert>\n";
	$xml_soap_request .= "  </soap:Body>\n";
	$xml_soap_request .= "</soap:Envelope>";

	$response = send_911_api($xml_soap_request);

	$response_code = isset($response['soap:Body']['Query911AlertResponse']['Query911AlertResult']['responseCode'])?$response['soap:Body']['Query911AlertResponse']['Query911AlertResult']['responseCode']:False;

	if ($response_code != '100') {
		return False;
	}

	$email = isset($response['soap:Body']['Query911AlertResponse']['Query911AlertResult']['Alerts']['string'])?$response['soap:Body']['Query911AlertResponse']['Query911AlertResult']['Alerts']['string']:False;
	
	return $email;
}

function send_911_api($xml_soap_request) {

	$curl = curl_init();

	curl_setopt_array($curl, array(
		CURLOPT_URL => $_SESSION['e911']['api_url']['text'],
	  	CURLOPT_RETURNTRANSFER => true,
	  	CURLOPT_ENCODING => "",
	  	CURLOPT_MAXREDIRS => 10,
	  	CURLOPT_TIMEOUT => 10,
	  	CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	  	CURLOPT_CUSTOMREQUEST => "POST",
	  	CURLOPT_POSTFIELDS => $xml_soap_request, 
	  	CURLOPT_HTTPHEADER => array(
	    	"cache-control: no-cache",
	    	"content-type: text/xml",
	  	),
	));

	$response = curl_exec($curl);
	$err = curl_error($curl);

	curl_close($curl);

	//file_put_contents('/tmp/api_calls.log', "[REQUEST]: ".json_encode($xml_soap_request)."\n", FILE_APPEND);
	if ($err) {
		//file_put_contents('/tmp/api_calls.log', "[ERROR]".json_encode($err)."\n", FILE_APPEND);
		return False;
	}
	$response = xmlstr_to_array($response);
	//file_put_contents('/tmp/api_calls.log', "[RESPONSE]: ".json_encode($response)."\n", FILE_APPEND);
	return $response;

}

// High level function here

function update_e911($e911_data) {

	$response = array();
	$e911_did = $e911_data['e911_did'];
	$e911_alert_email = $e911_data['e911_alert_email'];
	$e911_alert_email_enable = $e911_data['e911_alert_email_enable'];

	$action = False;
	$old_data = query_e911_data($e911_did);

	if ($old_data) {
		if ( $old_data['e911_address_1'] == $e911_data['e911_address_1'] &&
			 $old_data['e911_address_2'] == $e911_data['e911_address_2'] &&
			 $old_data['e911_city'] == $e911_data['e911_city'] &&
			 $old_data['e911_state'] == $e911_data['e911_state'] &&
			 $old_data['e911_zip'] == $e911_data['e911_zip'] &&
			 $old_data['e911_zip_4'] == $e911_data['e911_zip_4'] &&
			 $old_data['e911_callername'] == $e911_data['e911_callername']) {
		} else {
			$action = 'update';
		}
	} else {
		$action = 'add';
	}

	if ($action) {
		if (validate_e911_data($e911_data)) {
			if ($action == 'add') {
				if (add_e911_data($e911_data)) {
					$response['e911_validated'] = 'Added';
				} else {
					$response['e911_validated'] = 'Not added';
					$response['e911_alert_email_enable'] = 'false';
				}
			} else {
				if (update_e911_data($e911_data)) {
					$response['e911_validated'] = 'Updated';
				} else {
					$response['e911_validated'] = 'Not added';
					$response['e911_alert_email_enable'] = 'false';
				}
			}

		} else {
			$response['e911_validated'] = 'Not validated';
			$response['e911_alert_email_enable'] = 'false';
			//remove_e911_data($e911_data); - Big question here
			return $response;
		}
	} else {
		$response['e911_validated'] = isset($old_data['e911_validated'])?$old_data['e911_validated']:"No information";
	}

	if ($e911_alert_email_enable == "true") {
		if (query_e911_alert($e911_did) == $e911_alert_email) {
			$response['e911_alert_email_enable'] = "true";
		} else {
			remove_e911_alert($e911_did);
			if (add_e911_alert($e911_did, $e911_alert_email)) {
				$response['e911_alert_email_enable'] = "true";
			} else {
				$response['e911_alert_email_enable'] = "false";
			}
		}
	} else {
		$response['e911_alert_email_enable'] = "false";
	}

	return $response;
}

?>
