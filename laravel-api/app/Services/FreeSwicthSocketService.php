<?php

namespace App\Services;

use Illuminate\Support\Arr;
use Api\Settings\Models\Setting;

class FreeSwicthSocketService
{
    public function __construct()
    {
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

            $settings = new Setting;
            $settings = $settings->first();

            $_SESSION['event_socket_ip_address'] = $settings->event_socket_ip_address;
            $_SESSION['event_socket_port'] = $settings->event_socket_port;
            $_SESSION['event_socket_password'] = $settings->event_socket_password;

            Arr::set($_SESSION, 'cache.method.text', 'memcache');
        } else {
            if (isset($this->event_socket_ip_address)) {
                return;
            }

            $settings = new Setting;
            $settings = $settings->first();

            $this->event_socket_ip_address = $settings->event_socket_ip_address;
            $this->event_socket_port = $settings->event_socket_port;
            $this->event_socket_password = $settings->event_socket_password;
        }


        /* Maybe we don't need to load all these variable to var
      //get the default settings
      $settings = new DefaultSetting;
      $result = $settings->orderBy('default_setting_order', 'asc')->get()->toArray();

      //unset all settings
      foreach ($result as $row) {
        unset($_SESSION[$row['default_setting_category']]);
      }
      //set the enabled settings as a session
      foreach ($result as $row) {
        if ($row['default_setting_enabled'] == 'true') {
          $name = $row['default_setting_name'];
          $category = $row['default_setting_category'];
          $subcategory = $row['default_setting_subcategory'];
          if (strlen($subcategory) == 0) {
            if ($name == "array") {
              $_SESSION[$category][] = $row['default_setting_value'];
            }
            else {
              $_SESSION[$category][$name] = $row['default_setting_value'];
            }
          }
          else {
            if ($name == "array") {
              $_SESSION[$category][$subcategory][] = $row['default_setting_value'];
            }
            else {
              $_SESSION[$category][$subcategory]['uuid'] = $row['default_setting_uuid'];
              $_SESSION[$category][$subcategory][$name] = $row['default_setting_value'];
            }
          }
        }
      }
      */
    }
}
