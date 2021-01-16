<?php

namespace Infrastructure;

class Application extends \Illuminate\Foundation\Application
{
    /**
     * Overrides the path to the application "app" directory.
     *
     * @return string
     */
    public function path($path = '')
    {
        $this->useAppPath($this->basePath.DIRECTORY_SEPARATOR.'infrastructure');
        return parent::path($path);
    }
}