<?php

namespace App;

class Application extends \Illuminate\Foundation\Application
{
    /**
     * Overrides the path to the application "app" directory.
     *
     * @return string
     */
    public function path($path = '')
    {
        $this->useAppPath($this->basePath . DIRECTORY_SEPARATOR . 'app');
        return parent::path($path);
    }
}
