<?php
/*
 * Functions copied from fusionpbx/resources/switch.php
 *
 * NOTE. We cannot encapsulate the functions into a class
 * (Gruz tried to encapsulate to laravel-api/app/Providers/FreeSwicthSocketServiceProvider.php)
 * because  we also call other FusionFPX native functions (like cache) wich reference to this functions.
 * So to follow DRY we use it a non OOP procedures here
 * */
if (!function_exists('event_socket_create')) {
    require config('app.fpath_document_root') . '/resources/classes/event_socket.php';

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
