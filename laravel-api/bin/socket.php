<?php
function config() {
    return '/var/www/fusionpbx';
}
// require_once 'infrastructure/Helpers/event_socket_helper.php';

class FreeSwicthSocketService
{
    private $settings;

    public function __construct($settings)
    {
        $this->settings = new stdClass;
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
        $fp = $this->event_socket_create();
        // $response = event_socket_request($fp, 'api flush cache');
        $response = event_socket_request($fp, 'api reloadxml');

        fclose($fp);

        return $response;
    }

    /**
     * Clears a FreeSwitch cache folder
     *
     * Uses native FusionBPX class. So needs to set some $_SESSION variables to make it work
     *
     * @param   string  $uri  URI to be passed to socket.
     *
     * @return   string  Socket response
     */
    public function clearCache($uri)
    {
        // We need to load params into the $_SESSION var here to make native FusionPBX class fire
        $this->loadSocketParams(true);
        $file = config('app.fpath_document_root') . '/resources/classes/cache.php';
        require_once $file;

        //clear the cache
        $cache = new \cache;
        // $cache->delete($uri);
        $response = $cache->flush();

        return $response;
    }

    /**
     * Create a socker resource using native fusionpbx approach
     *
     * Full description (multiline)
     *
     * @param   type  $name  Description
     *
     * @return   resource
     */
    protected function event_socket_create($host = null, $port = null, $password = null)
    {
        if (empty($host)) {
            $host = $this->event_socket_ip_address;
        }

        if (empty($port)) {
            $port = $this->event_socket_port;
        }

        if (empty($password)) {
            $password = $this->event_socket_password;
        }

        try {
            $fp = \event_socket_create($host, $port, $password);
        } catch (\Exception $e) {
            throw $e;
        }

        return $fp;
    }

    public function mkdir(string $dir)
    {
        if (strlen($dir) > 0 && !is_readable($dir)) {
            //connect to fs
            $fp = $this->event_socket_create();

            if (!$fp) {
                return false;
            }

            //send the mkdir command to freeswitch
            if ($fp) {
                //build and send the mkdir command to freeswitch
                $switch_cmd = "lua mkdir.lua '$dir'";
                $switch_result = event_socket_request($fp, 'api ' . $switch_cmd);
                fclose($fp);
                //check result
                if (trim($switch_result) == "-ERR no reply") {
                    return true;
                }
            }
            //can not create directory
            return false;
        }

        return true;
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
    public function loadSocketParams($toSession = false)
    {
        // We need to set this wars to reuse FusionPBX code in fusionpbx/resoures/switch.php
        if ($toSession) {
            if ((isset($_SESSION)) or isset($_SESSION['event_socket_ip_address'])) {
                return;
            }

            $_SESSION['event_socket_ip_address'] = $this->settings->event_socket_ip_address;
            $_SESSION['event_socket_port'] = $this->settings->event_socket_port;
            $_SESSION['event_socket_password'] = $this->settings->event_socket_password;

            $_SESSION['cache']['method']['text'] = 'memcache';
        } else {
            if (isset($this->event_socket_ip_address)) {
                return;
            }

            $this->event_socket_ip_address = $this->settings->event_socket_ip_address;
            $this->event_socket_port = $this->settings->event_socket_port;
            $this->event_socket_password = $this->settings->event_socket_password;
        }
    }
}


if (!function_exists('event_socket_create')) {
    $path = config('app.fpath_document_root');
    $file = $path . '/resources/classes/event_socket.php';
    require $file;

    /* ##mygruz20170512010935 {
    It was:
    It became: */
    function generateCallTrace()
    {
        $e = new Exception();
        $trace = explode("\n", $e->getTraceAsString());
        // reverse array to make steps line up chronologically
        $trace = array_reverse($trace);
        array_shift($trace); // remove {main}
        array_pop($trace); // remove call to this method
        $length = count($trace);
        $result = array();

        for ($i = 0; $i < $length; $i++) {
            $result[] = ($i + 1)  . ')' . substr($trace[$i], strpos($trace[$i], ' ')); // replace '#someNum' with '$i)', set the right ordering
        }

        return "\n\t" . implode("\n\t", $result);
    }
    /* ##mygruz20170512010935 } */

    function event_socket_create($host, $port, $password)
    {
        // if (env('APP_DEBUG'))
        // {
        //   file_put_contents('/var/www/fusionpbx/log_laravel.txt', 'trace: '.generateCallTrace() . PHP_EOL, FILE_APPEND);
        //   file_put_contents('/var/www/fusionpbx/log_laravel.txt', 'create: '.$host.':'.$password.':'.$port . PHP_EOL, FILE_APPEND);
        // }

        $esl = new event_socket;
        if ($esl->connect($host, $port, $password)) {
            return $esl->reset_fp();
        }
        return false;
    }

    function event_socket_request($fp, $cmd)
    {
        $esl = new event_socket($fp);
        $result = $esl->request($cmd);
        $esl->reset_fp();

        // if (env('APP_DEBUG')) {
        //     file_put_contents('/var/www/fusionpbx/log_laravel.txt', 'trace: ' . generateCallTrace() . PHP_EOL, FILE_APPEND);
        //     file_put_contents('/var/www/fusionpbx/log_laravel.txt', 'request: ' . $cmd . PHP_EOL, FILE_APPEND);
        //     file_put_contents('/var/www/fusionpbx/log_laravel.txt', 'result: ' . print_r($result, true) . PHP_EOL, FILE_APPEND);
        // }

        return $result;
    }
}

$settings = [
    'event_socket_ip_address' => '127.0.0.1',
    'event_socket_port' => '8021',
    'event_socket_password' => 'ClueCon',
];

$socket = new FreeSwicthSocketService($settings);
$response = $socket->clearCache(true);
echo $response . PHP_EOL;
$response = $socket->reloadXML();
echo $response . PHP_EOL;
