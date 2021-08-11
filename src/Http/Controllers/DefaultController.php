<?php

namespace Gruz\FPBX\Http\Controllers;

use Gruz\FPBX\Version;
use OpenApi\Annotations as OA;

/**
 * @OA\Info(
 *   title="FusionPBX API",
 *   version=SW_VERSION,
 *   @OA\Contact(
 *     email="vongruz@protonmail.com"
 *   )
 * )
 *   @OA\Server(url=APP_URL)
 */

/**
 * @OA\Tag(
 *     name="Default",
 * )
 * @OA\Tag(
 *     name="Domain",
 *     description="Manage domains",
 * )
 * @OA\Tag(
 *     name="User",
 *     description="Manage users",
 * )
 * @OA\Tag(
 *     name="Extension",
 *     description="Manage extensions",
 * )
*/

/**
 * @OA\SecurityScheme(
 *     type="http",
 *     scheme="bearer",
 *     securityScheme="bearer_auth",
 * )
 */

class DefaultController extends AbstractBrunoController
{
    /**
     * @OA\Get(
     *  tags={"Default"},
     *  description="Get application name and version",
     *  x={"route-$middlewares"="api"},
     *  path="/",
     *  security={},
     *      @OA\Response(
     *      description="Application name and version",
     *      response=200,
     *      @OA\MediaType(
     *           mediaType="application/json",
     *           @OA\Schema(
     *               @OA\Property(property="title", type="string", description="APP_NAME variable"),
     *               @OA\Property(property="version", type="string", description="Git tag version")
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
