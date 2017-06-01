<?php

namespace App\Api\Controllers;

use App\Http\Controller as BaseController;
use App\Version;

class DefaultApiController extends BaseController
{
    public function index()
    {
        return response()->json([
            'title'   => 'FusionPBX API',
            'version' => Version::getGitTag()
        ]);
    }
}
