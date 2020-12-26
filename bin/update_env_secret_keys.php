#!/usr/bin/php
<?php
/**
 * It's a helper function to update larvael .env file automatically.
 * It's written in a PHP because I have lack knowledge of `bash` how to do the same.
 *
 * @author AHeavyObject <vongruz@protonmail.com>
 */

echo getcwd();
$tmp = file_get_contents('tmp.txt');
$tmp = explode(PHP_EOL, $tmp);
$c = count($tmp);
$tmp2 = [
    $tmp[$c-5],
    $tmp[$c-2],
];
$keys = array_map(function($item) {
    $item = explode(' ', $item);
    return $item = end($item);;
}, $tmp2);

echo PHP_EOL. ' Updating .env file with keys ' . PHP_EOL;
foreach ($keys as $key) {
    echo 'PERSONAL_CLIENT_SECRET=' . $key . PHP_EOL;
}

$filePath = $argv[1]. '/.env';
$env = file_get_contents($filePath);
$env = explode(PHP_EOL, $env);
$i = 0;

foreach ($env as $key => $value) {
    if (0 === strpos($value, 'PERSONAL_CLIENT_SECRET=')) {
        $env[$key] = 'PERSONAL_CLIENT_SECRET=' . $keys[$i];
        $i++;
    }
}

$env = implode(PHP_EOL, $env);
file_put_contents($filePath, $env);
unlink('tmp.txt');