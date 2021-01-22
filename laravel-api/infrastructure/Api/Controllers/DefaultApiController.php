<?php

namespace Infrastructure\Api\Controllers;

use Infrastructure\Http\Controller as BaseController;
use Infrastructure\Version;
use OpenApi\Annotations as OA;

/**
 * @OA\Info(
 *   title="FusionPBX API",
 *   version=SW_VERSION,
 *   @OA\Contact(
 *     email="vongruz@protonmail.com"
 *   )
 * )
 */

class DefaultApiController extends BaseController
{
    /**
     * @OA\Get(
     *   path="/",
     *      @OA\Response(
     *      response=200,
     *      description="Get application name and version",
     *      @OA\MediaType(
     *           mediaType="application/json",
     *           @OA\Schema(
     *               @OA\Property(property="title", type="string", description="APP_NAME variable"),
     *               @OA\Property(property="version", type="string", description="Fit tag version")
     *           ),
     *           @OA\Examples(example="Asdsads", summary="App info", value={"title": "FusionPBX API", "version" : "0.0.8-62-g192d97b"}),
     *      )
     *   )
     * )
     */
    public function index()
    {
        return response()->json([
            'title'   => 'FusionPBX API',
            'version' => Version::getGitTag()
        ]);
    }
}
