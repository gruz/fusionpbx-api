<?php

class fpbx_hook
{
    private $settings;

    public function __construct($fusion_path)
    {
        $file = $this->settings = new stdClass;

        $files = [
            '/resources/switch.php',
            '/resources/functions.php',
            '/resources/classes/database.php',
            '/resources/classes/event_socket.php',
            '/resources/classes/cache.php',
        ];

        foreach ($files as $file) {
            $_SERVER["PHP_SELF"] = $fusion_path . $file;
            $_SERVER["SCRIPT_FILENAME"] = basename($fusion_path . $file);
            $path = $fusion_path . $file;
            require_once $path;
        }

        $settings = [];

        $sql = "select * from v_default_settings ";
        $sql .= "where default_setting_category = :value ";
        $parameters['value'] = 'cache';
        $database = new database;
        $rows = $database->select($sql, $parameters);
        foreach ($rows as $key => $row) {
            switch ($row['default_setting_subcategory']) {
                case 'method':
                    $settings['cache_type'] = $row['default_setting_value'];
                    break;
                case 'location':
                    $settings['cache_path'] = $row['default_setting_value'];
                    break;
            }
        }

        $sql = "select * from v_settings ";
        $database = new database;
        $rows = $database->select($sql);
        $row = $rows[0];

        $settings['event_socket_ip_address'] = $row['event_socket_ip_address'];
        $settings['event_socket_password'] = $row['event_socket_password'];
        $settings['event_socket_port'] = $row['event_socket_port'];

        foreach ($settings as $key => $value) {
            $this->settings->$key = $value;
        }

        $this->loadSocketParams();
    }
    /**
     * Reloads FreeSwitch XML
     *
     * @return   string  Request respons
     */
    public function reloadXML()
    {
        $fp = event_socket_create($this->settings->event_socket_ip_address, $this->settings->event_socket_port, $this->settings->event_socket_password);

        $response = event_socket_request($fp, 'api reloadxml');

        fclose($fp);

        return $response;
    }

    public function clearCache($uri = null)
    {
        $cache = new \cache;
        $response = $cache->flush();

        return $response;
    }


    /**
     * Set parameters needed to create socket resources based on FusionPBX configuration
     *
     * Full description (multiline)
     *
     * @param   bool  $toSession  Whether to set the parameters to the $_SESSION (for native FusionPBX code)
     *                            or to the class variables
     *
     * @return   void
     */
    public function loadSocketParams()
    {
        $_SESSION['event_socket_ip_address'] = $this->settings->event_socket_ip_address;
        $_SESSION['event_socket_port'] = $this->settings->event_socket_port;
        $_SESSION['event_socket_password'] = $this->settings->event_socket_password;

        $_SESSION['cache']['method']['text'] = $this->settings->cache_type;
        $_SESSION['cache']['location']['text'] = $this->settings->cache_path;
    }
}

$fusionpbx_path = empty($argv[1]) ? '/var/www/fusionpbx' : $argv[1];

$socket = new fpbx_hook($fusionpbx_path);
$response = $socket->clearCache();
echo $response . PHP_EOL;
$response = $socket->reloadXML();
echo $response . PHP_EOL;
