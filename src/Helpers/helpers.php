<?php

// $api_root = realpath(__DIR__ . '/../../');
// $path = realpath($api_root . '/../');
// if ('/var/www/fusionpbx-api' === $path) {
//     $path = '/var/www';
// }

// require_once $path . "/fusionpbx/resources/functions.php";
// require_once $path . "/fusionpbx/resources/classes/message.php";

/**
 * Helper functions for user
 */

/**
 * Function to generate password for user. As FusionPBX has it`s own vision
 * how password should be generated and encrypted so we shouldn`t break that logic.
 * This should be used instead of Laravel methods.
 * Function generates salt and calculates the md5 hash of password and salt combination.
 *
 * @param string $password User passowrd
 * @return array|null ['password' => 'value', 'salt' => 'value'] or null.
 */
if (!function_exists('encrypt_password_with_salt')) {

    function encrypt_password_with_salt($password, $salt = null)
    {
        if (!empty($password) && !is_null($password)) {
            $data['salt'] = is_null($salt) || empty($salt)
                                ? \Str::uuid()->toString()
                                : $salt;
            $data['password'] = md5($data['salt'] . $password);

            return $data;
        }

        return null;
    }
}


if (!function_exists('get_composer_json_namespaces')) {

    function get_composer_json_namespaces()
    {
        static $storage = false;

        if (false !== $storage) {
            return $storage;
        }

        $path = __DIR__ . '/../../';
        $composer_json = json_decode(file_get_contents($path . 'composer.json'));

        $psr4 = $composer_json->autoload->{'psr-4'};
        $namespaces = [];
        foreach ($psr4 as $prefix => $folder) {
            if (strpos($prefix, 'Database') === 0) {
                continue;
            }

            $prefix = rtrim($prefix, '\\');
            $folder = rtrim($folder, '/');

            $namespaces[$prefix] = realpath($path . '/' . $folder);
        }

        $storage = $namespaces;

        return $namespaces;
    }
}
