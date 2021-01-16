<?php

namespace Infrastructure\Api\Controllers;

use Infrastructure\Http\Controller as BaseController;
use Infrastructure\Version;

class DefaultApiController extends BaseController
{
    public function index()
    {
        $a = now();
        return response()->json([
            'title'   => 'FusionPBX API',
            'version' => Version::getGitTag()
        ]);
    }
}
