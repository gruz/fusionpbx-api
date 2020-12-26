#!/usr/bin/php
<?php
/**
 * It's a helper function to update larvael .env file automatically.
 * It's written in a PHP because I have lack knowledge of `bash` how to do the same.
 *
 * @author AHeavyObject <vongruz@protonmail.com>
 */

require '/etc/fusionpbx/config.php';

$filePath = $argv[1]. '/.env';
$env = file_get_contents($filePath);
$env = explode(PHP_EOL, $env);

foreach ($env as $key => $value) {
    if (0 === strpos($value, 'DB_PASSWORD=')) {
        $env[$key] = 'DB_PASSWORD=' . $db_password;
        break;
    }
}

$env = implode(PHP_EOL, $env);
file_put_contents($filePath, $env);
