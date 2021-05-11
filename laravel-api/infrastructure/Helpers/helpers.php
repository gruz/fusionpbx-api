<?php

$api_root = realpath(__DIR__ . '/../../');
$path = realpath($api_root . '/../');
if ('/var/www/fusionpbx-api' === $path) {
    $path = '/var/www';
}

require_once $path . "/fusionpbx/resources/functions.php";
require_once $path . "/fusionpbx/resources/classes/message.php";

if (!function_exists('get_composer_json_namespaces')) {

    function get_composer_json_namespaces()
    {
        static $storage = false;

        if (false !== $storage) {
            return $storage;
        }

        $composer_json = json_decode(file_get_contents(base_path() . '/' . 'composer.json'));

        $psr4 = $composer_json->autoload->{'psr-4'};
        $namespaces = [];
        foreach ($psr4 as $prefix => $folder) {
            if (strpos($prefix, 'Database') === 0) {
                continue;
            }

            $prefix = rtrim($prefix, '\\');
            $folder = rtrim($folder, '/');

            $namespaces[$prefix] = base_path() . '/' . $folder;
        }

        $storage = $namespaces;

        return $namespaces;
    }
}
