<?php

namespace Gruz\FPBX\Helpers;

class Version
{
    public static function getGitTag()
    {
        $versionFile = base_path('version.txt');
        return file_exists($versionFile) ? trim(file_get_contents($versionFile)) : exec('git describe --tags');
    }

    public static function getPackageVersion()
    {
        $getGitTag = self::getGitTag();

        if (!empty($getGitTag)) {
            return $getGitTag;
        }

        $packages = json_decode(file_get_contents(__DIR__ . '/../../../../composer/installed.json'));
        $packages = collect($packages->packages);

        return $packages->where('name', 'gruz/fusionpbx-api')->first()->version;
    }
}
