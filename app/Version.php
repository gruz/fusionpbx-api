<?php

namespace App;

class Version
{
    public static function getGitTag()
    {
        $versionFile = base_path('version.txt');
        return file_exists($versionFile) ? trim(file_get_contents($versionFile)) : exec('git describe --tags');
    }
}
