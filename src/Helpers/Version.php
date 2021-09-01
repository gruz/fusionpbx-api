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
        return 'none';
        $getGitTag = self::getGitTag();

        if (!empty($getGitTag)) {
            return $getGitTag;
        }

        $file = __DIR__ . '/../../../../composer/installed.json';
        if (file_exists($file)) {
            $packages = json_decode($file);
            $packages = collect($packages->packages);
            $version = $packages->where('name', 'gruz/fusionpbx-api')->first()->version;
        } else {
            $version = 'unknown version';
        }

        return $version;
    }
}
