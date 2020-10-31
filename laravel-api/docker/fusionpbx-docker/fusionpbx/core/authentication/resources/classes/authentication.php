<?php

/**
 * authentication 
 *
 * @method validate uses authentication plugins to check if a user is authorized to login
 * @method get_domain used to get the domain name from the URL or username and then sets both domain_name and domain_uuid
 */
class authentication {

	/**
	 * Define variables and their scope
	 */
	public $debug;
	public $db;
	public $domain_uuid;
	public $domain_name;
	public $username;
	public $password;
	public $plugins;
	public $key;

	/**
	 * Called when the object is created
	 */
	public function __construct() {

	}

	/**
	 * Called when there are no references to a particular object
	 * unset the variables used in the class
	 */
	public function __destruct() {
		foreach ($this as $key => $value) {
			unset($this->$key);
		}
	}

	/**
	 * validate uses authentication plugins to check if a user is authorized to login
	 * @return array [plugin] => last plugin used to authenticate the user [authorized] => true or false
	 */
	public function validate() {

		//set the default authentication method to the database
			if (!is_array($_SESSION['authentication']['methods'])) {
				$_SESSION['authentication']['methods'][]  = 'database';	
			}

		//get the domain_name and domain_uuid
			if (!isset($this->domain_name) || !isset($this->domain_uuid)) {
				$this->get_domain();
			}

		//set the database as the default plugin
			if (!isset($_SESSION['authentication']['methods'])) {
				$_SESSION['authentication']['methods'][] = 'database';
			}

		//use the authentication plugins
			foreach ($_SESSION['authentication']['methods'] as $name) {
				$class_name = "plugin_".$name;
				$base = realpath(dirname(__FILE__)) . "/plugins";
				$plugin = $base."/".$name.".php";
				if (file_exists($plugin)) {
					include_once $plugin;
					$obj = new $class_name();
					$obj->debug = $this->debug;
					$obj->domain_name = $this->domain_name;
					$obj->domain_uuid = $this->domain_uuid;
					if (strlen($this->key) > 0) {
						$obj->key = $this->key;
					}
					if (strlen($this->username) > 0) {
						$obj->username = $this->username;
						$obj->password = $this->password;
					}
					$array = $obj->$name();
					$result['plugin'] = $array["plugin"];
					$result['domain_name'] = $array["domain_name"];
					$result['username'] = $array["username"];
					if ($this->debug) {
						$result["password"] = $this->password;
					}
					$result['user_uuid'] = $array["user_uuid"];
					$result['contact_uuid'] = $array["contact_uuid"];
					$result['domain_uuid'] = $array["domain_uuid"];
					$result['authorized'] = $array["authorized"];
					if (count($_SESSION['authentication']['methods']) > 1) {
						$result['results'][] = $array;
					}
					if ($result["authorized"] == "true") {
						$_SESSION['username'] = $result["username"];
						break;
					}
				}
			}

		//return the result
			return $result;
	}

	/**
	 *  get_domain used to get the domain name from the URL or username and then sets both domain_name and domain_uuid
	 */
	function get_domain() {

		//get the domain from the url
			$this->domain_name = $_SERVER["HTTP_HOST"];

		//get the domain name from the username
			if ($_SESSION["user"]["unique"]["text"] != "global") {
				$username_array = explode("@", check_str($_REQUEST["username"]));
				if (count($username_array) > 1) {
					//get the domain name
						$domain_name =  $username_array[count($username_array) -1];
					//check if the domain from the username exists then set the domain_uuid
						$domain_exists = false;
						foreach ($_SESSION['domains'] as $row) {
							if (lower_case($row['domain_name']) == lower_case($domain_name)) {
								$this->domain_uuid = $row['domain_uuid'];
								$domain_exists = true;
								break;
							}
						}
					//if the domain exists then set domain_name and update the username
						if ($domain_exists) {
							$this->domain_name = $domain_name;
							$this->username = substr(check_str($_REQUEST["username"]), 0, -(strlen($domain_name)+1));
							$_SESSION['domain_uuid'] = $this->domain_uuid;
						}
					//unset the domain name variable
						unset($domain_name);
				}
			}

		//get the domain name from the http value
			if (strlen(check_str($_REQUEST["domain_name"])) > 0) {
				$this->domain_name = check_str($_REQUEST["domain_name"]);
			}

		//remote port number from the domain name
			$domain_array = explode(":", $this->domain_name);
			if (count($domain_array) > 1) {
				$this->domain_name = $domain_array[0];
			}

		//get the domain uuid and domain settings
			if (isset($this->domain_name) && !isset($this->domain_uuid)) {
				foreach ($_SESSION['domains'] as $row) {
					if (lower_case($row['domain_name']) == lower_case($this->domain_name)) {
						$this->domain_uuid = $row['domain_uuid'];
						$_SESSION['domain_uuid'] = $row['domain_uuid'];
						break;
					}
				}
			}

		//set the setting arrays
			$obj = new domains();
			$obj->db = $db;
			$obj->set();

		//set the domain settings
			$_SESSION['domain_name'] = $this->domain_name;
			$_SESSION['domain_parent_uuid'] = $_SESSION["domain_uuid"];

		//set the domain name
			return $this->domain_name;
	}
}

/*
$auth = new authentication;
$auth->username = "user";
$auth->password = "password";
$auth->domain_name = "sip.fusionpbx.com";
$auth->debug = false;
$response = $auth->validate();
print_r($response);
*/

?>
