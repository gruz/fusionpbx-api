<?php

// Adding 'hidden' or 'joined' attribute to xml array


if (!class_exists('xml_cdr_join_view')) {

    class xml_cdr_join_view {

        private $enabled;
        private $is_uuid;
        private $is_close_match;
        private $is_loose_race;

        public function __construct($session_options) {

            if (!$session_options || !isset($session_options['join_view'])) {
                $this->enabled = false;
                return;
            }

            if ($session_options['join_view']['uuid'] == '') {
                $this->enabled = false;
                return;
            }

            $this->enabled = true;

            $options = $session_options['join_view']['text'];
            
            $this->is_uuid = (strpos($options, "uuid") !== false);
            $this->is_close_match = (strpos($options, "close_match") !== false);
            $this->is_lose_race = (strpos($options, "lose_race") !== false);

            return;
        }

        private function xlog($str) {
            file_put_contents("/tmp/xml_cdr_join_view.log", $str, FILE_APPEND);
        }

        private function uuid_cleanup(&$xml_cdr_data) {
            // Yes, here we have 0^2 complexity. I know, but it's really quick'n'dirty way of doing this
            // Plus we're using it not more than on ~50-100 array size, so not super big data.
          
            foreach ($xml_cdr_data as $xml_cdr_data_parent_key => $xml_cdr_data_parent_value) {

                // Not process already marked data
                if (isset($xml_cdr_data_parent_value['hidden']) || isset($xml_cdr_data_parent_value['joined'])) {
                    continue;
                }

                $xml_cdr_parent_json_data = json_decode($xml_cdr_data_parent_value['json'], true);

                // First - check if it's child for others. If originating_leg_uuid is present - than this is child.
                $child_originating_leg_uuid = (isset($xml_cdr_parent_json_data['variables']['originating_leg_uuid'])) ? $xml_cdr_parent_json_data['variables']['originating_leg_uuid'] : false;

                // This is a child channel. We need to find a parent. For this channel could be only 1 parent.
                if ($child_originating_leg_uuid) {
                    // Here we cycle again to find master channel. Master uuid could be found on 3 variables;
                    // xml_cdr_uuid, uuid, channel_uuid
                    foreach ($xml_cdr_data as $k => $v) {

                        // Bypass myself
                        if ($k == $xml_cdr_data_parent_key) {
                            continue;
                        }

                        $possible_parent_channel_xml_cdr_uuid = $v['xml_cdr_uuid'];
                        // We found our master!
                        if ($possible_parent_channel_xml_cdr_uuid == $child_originating_leg_uuid) {
                            $xml_cdr_data[$k]['joined'] = true;
                            $xml_cdr_data[$xml_cdr_data_parent_key]['hidden'] = true;
                            continue 2; // Go for outer foreach loop. This master is already marked
                        }

                        // Check further.
                        $possible_parent_channel_json_data = json_decode($v['json'], true);

                        // Check uuid variable of the channel
                        $possible_parent_channel_uuid = (isset($possible_parent_channel_json_data['variables']['uuid'])) ? $possible_parent_channel_json_data['variables']['uuid'] : false;
                        if ($possible_parent_channel_uuid == $child_originating_leg_uuid) {
                            $xml_cdr_data[$k]['joined'] = true;
                            $xml_cdr_data[$xml_cdr_data_parent_key]['hidden'] = true;
                            continue 2; // Go for outer foreach loop. This master is already marked
                        }

                        // Check call_uuid variable of the channel
                        $possible_parent_channel_uuid = (isset($possible_parent_channel_json_data['variables']['call_uuid'])) ? $possible_parent_channel_json_data['variables']['call_uuid'] : false;
                        if ($possible_parent_channel_uuid == $child_originating_leg_uuid) {
                            $xml_cdr_data[$k]['joined'] = true;
                            $xml_cdr_data[$xml_cdr_data_parent_key]['hidden'] = true;
                            continue 2; // Go for outer foreach loop. This master is already marked
                        }
                    } // End inner foreach
                } // End if ($child_originating_leg_uuid)
            } // End outer foreach

        }

        private function lose_race_cleanup(&$xml_cdr_data) {
            foreach ($xml_cdr_data as $xml_cdr_data_key => $xml_cdr_data_line) {

                if (isset($xml_cdr_data_line['hidden']) || isset($xml_cdr_data_line['joined'])) {
                    continue;
                }

                if ($xml_cdr_data_line['hangup_cause'] == 'LOSE_RACE') {
                    $xml_cdr_data[$xml_cdr_data_key]['hidden'] = true;
                }
            }
        }

        private function close_match_cleanup(&$xml_cdr_data) {
            // Nothing here yet. TBD
        }


        public function status() {
            return $this->enabled;
        }

        public function cleanup(&$xml_cdr_data) {

            if (!$this->enabled) {
                return;
            }

            if ($this->is_uuid) {
                $this->uuid_cleanup($xml_cdr_data);
            }

            if ($this->is_close_match) {
                $this->close_match_cleanup($xml_cdr_data);
            }

            if ($this->is_lose_race) {
                $this->lose_race_cleanup($xml_cdr_data);
            }
        }
    }
}
?>