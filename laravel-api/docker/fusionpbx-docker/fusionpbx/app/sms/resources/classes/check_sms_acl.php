<?php

if (!class_exists('check_sms_acl')) {
	class check_sms_acl {

		private $acl_nodes;

		public function __construct($db, $domain_name) {

			// Example of output
			//default_policy	node_policy		node_cidr
			//deny				deny			1.1.1.1/32
			//deny				allow			0.0.0.0/0

			$sql =  "SELECT v_access_controls.access_control_default as default_policy,";
			$sql .= " v_access_control_nodes.node_type as node_policy,";
			$sql .= " v_access_control_nodes.node_cidr as node_cidr ";
			$sql .= "FROM v_access_control_nodes JOIN v_access_controls";
			$sql .= " ON v_access_controls.access_control_uuid = v_access_control_nodes.access_control_uuid ";
			$sql .= "WHERE v_access_controls.access_control_name = 'sms'";
			$sql .= " AND v_access_control_nodes.node_domain = '" . $domain_name . "'";
			$sql .= " ORDER BY node_policy DESC";

			$prep_statement = $db->prepare(check_sql($sql));
			$prep_statement->execute();
			$result = $prep_statement->fetchAll(PDO::FETCH_NAMED);

			if (count($result) == 0) {
				return;
			}
			
			$this->acl_nodes = $result;
		}

		private function single_check($ip, $cidr) {
			list ($net, $mask) = explode("/", $cidr);
		
			$ip_net = ip2long ($net);
			$ip_mask = ~((1 << (32 - $mask)) - 1);
		
			$ip_ip = ip2long ($ip);
		
			$ip_ip_net = $ip_ip & $ip_mask;
		
			return ($ip_ip_net == $ip_net);
		}

		public function check($ip) {

			if (!$this->acl_nodes) {
				return False;
			}

			$last_policy = 'deny';

			foreach ($this->acl_nodes as $acl_node) {
				
				$node_cidr = $acl_node['node_cidr'];
				$last_policy = $acl_node['default_policy'];

				if ($this->single_check($ip, $node_cidr)) {
					return $acl_node['node_policy'] == 'allow';
				}
			}

			return $last_policy == 'allow';
		}
	}
}
?>