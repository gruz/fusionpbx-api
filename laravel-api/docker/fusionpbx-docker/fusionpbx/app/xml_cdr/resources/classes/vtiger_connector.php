<?php


/* 		// Call VTiger API
        $vtiger_crm_connector_enable = isset($_SESSION['vtiger_connector']['enable']['boolean']) ? filter_var($_SESSION['vtiger_connector']['enable']['boolean'], FILTER_VALIDATE_BOOLEAN) : False;
        if ($vtiger_crm_connector_enable && strlen($start_stamp) > 0) {
            $vtiger_url = strlen($xml->variables->vtiger_url) > 0 ? base64_decode(urldecode($xml->variables->vtiger_url), true) : False;
            $vtiger_api_key = strlen($xml->variables->vtiger_api_key) > 0 ? base64_decode(urldecode($xml->variables->vtiger_api_key), true) : False;

            $vtiger_record_path = False;

            if (isset($database->fields['recording_file']) and strlen($xml->variables->vtiger_record_path) > 0) { 
                $vtiger_record_path = base64_decode(urldecode($xml->variables->vtiger_record_path)).$recording_relative_path.'/'.$uuid.$recording_extension;
            }

            $vtiger_api_call = new vtiger_connector($vtiger_url, $vtiger_api_key, $database->fields, $vtiger_record_path);
            if ($vtiger_api_call) {
                $vtiger_api_call->send();
            }
            unset($vtiger_url);
            unset($vtiger_api_key);
            unset($vtiger_record_path);
            unset($vtiger_api_call);
        } */

if (!class_exists('vtiger_connector')) {
	class vtiger_connector {

        private $url;
        private $key;
        private $fields;

        public $is_ready;

        public function __construct($session = False) {
            
            if (!$url or !$key) {
                return False;
            }

            $this->url = $url;
            $this->key = $key;

            $this->fields = array();
            $this->fields['timestamp'] = $database_fields['end_epoch'];
            $this->fields['direction'] = $database_fields['direction'];
            // Get correct hangup
            switch ($database_fields['hangup_cause']) {
                case 'NORMAL_CLEARING':
                    $this->fields['status'] = 'answered';
                    break;
                case 'CALL_REJECTED':
                case 'SUBSCRIBER_ABSENT':
                case 'USER_BUSY':
                    $this->fields['status'] = 'busy';
                    break;
                case 'NO_ANSWER':
                case 'NO_USER_RESPONSE':
                case 'ORIGINATOR_CANCEL':
                case 'LOSE_RACE': // This cause usually in ring groups, so this call is not ended.
                    $this->fields['status'] = 'no answer';
                    break;
                default:
                    $this->fields['status'] = 'failed';
                    break;
            }
            $src = array();
            $src['name'] = $database_fields['caller_id_name'];
            $src['number'] = $database_fields['caller_id_number'];
            $this->fields['src'] = $src;

            $last_seen = array();
            $last_seen['name'] = $database_fields['destination_number'];
            $last_seen['number'] = $database_fields['destination_number'];
            $this->fields['last_seen'] = $last_seen;

            $time = array();
            $time['duration'] = $database_fields['duration'];
            $time['answered'] = $database_fields['billsec'];
            $this->fields['time'] = $time;

            $this->fields['uuid'] = $database_fields['uuid'];

            if ($record_path) {
                $this->fields['recording'] = $record_path;
            }
            
            $this->is_ready = True;
        }

        # Just a failsafe not to throw an error
        public function __call($name, $arguments) {
            return;
        }


        public function process(&$xml_varibles) {
        }

        private function send() {

            if (empty($this->fields)) {
                return;
            }
            
            $data_string = json_encode($this->fields);

            $ch = curl_init($this->url.'/call_end.php');
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
            curl_setopt($ch, CURLOPT_HEADER, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_AUTOREFERER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 3);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json',
                                                    'Content-Length: ' . strlen($data_string)
                                                ));

            $resp = curl_exec($ch);
            curl_close($ch);

            file_put_contents('/tmp/api_vtiger.log', " -> ".$this->url.'/call_end.php'. " Req:".$data_string." Resp:".$resp."\n");

        }

/*         private function send_request($data) {

            $get_data = http_build_query($data);

            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => $this->crm_url . "/?" . $get_data,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 1,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "GET",
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);

            curl_close($curl);

            if ($err) {
                return False;
            }
            return $response;
        } */
    }
}


?>