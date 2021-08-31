<?php

namespace Gruz\FPBX\SwaggerProcessors;

use OpenApi\Analysis;
use Illuminate\Support\Arr;
use const OpenApi\UNDEFINED;
use OpenApi\Annotations\Items;
use OpenApi\Annotations\Schema;
use OpenApi\Annotations\Examples;
use OpenApi\Annotations\Property;
use OpenApi\Annotations\Response;
use OpenApi\Annotations\MediaType;
use OpenApi\Annotations\Operation;
use OpenApi\Annotations\Parameter;
use Gruz\FPBX\Models\AbstractModel;
use OpenApi\Annotations\Components;
use OpenApi\Processors\OperationId;
use Illuminate\Support\Facades\File;
use OpenApi\Annotations\JsonContent;
use OpenApi\Annotations\RequestBody;
use Illuminate\Support\Facades\Storage;
use OpenApi\Annotations\AbstractAnnotation;

/**
 * Custom processor to translate the vendor tag `query-args-$ref` into query parameter annotations.
 *
 * Details for the parameters are taken from the referenced schema.
 */
class SchemaQueryParameter
{
    // const MODEL_ADD_INCLUDES = 'model-add-includes';
    const ROUTE_PATH = 'route-$path';
    const ROUTE_MIDDLEWARES = 'route-$middlewares';
    const ROUTE_ACTION = 'route-$action';
    private $api_controllers_prefix;
    private $examplesPath;

    public function __invoke(Analysis $analysis)
    {
        $this->api_controllers_prefix = config('fpbx.namespace');
        $this->examplesPath = __DIR__.'/../../resources/swagger/examples';
        $this->registerRoutes($analysis);

        /**
         * @var Schema[]
         */
        $schemas = $analysis->getAnnotationsOfType(Schema::class, true);

        foreach ($schemas as $schema) {
            if ($schema->schema !== UNDEFINED) {
                $this->buildSchemaFromModel($schema);
            }
        }

        $this->makeOperationIdRedocCompatible($analysis);
        $this->addExamplesFromFiles($analysis);
        $this->attachCommonResponses($analysis);
    }

    protected function addExamplesFromFiles(Analysis $analysis)
    {
        $paths = $this->getAvailablePaths($analysis);

        foreach ($paths as $actionPath => $data) {
            $this->attachRequestExamples($actionPath, $data);
            $this->attachRepsonseExamples($actionPath, $data);
        }
    }
    protected function attachCommonResponses(Analysis $analysis) {
        $paths = $this->getAvailablePaths($analysis);
        foreach ($paths as $actionPath => $data) {
            $method = $data['method'];
            $path = $data['pathItem'];
            $annotation = $path->$method;
            if ($annotation->security !== UNDEFINED) {
                foreach ($annotation->security as $security) {
                    if (array_key_exists('bearer_auth', $security)) {
                        $this->attachUnauthenticatedResponse($annotation);
                        $this->attachUnverifiedEmailResponse($annotation);
                    }
                }
            }

            if (strpos($path->path, '{uuid}') !== false) {
                $this->attachBadUuidResponse($annotation);
                $this->attachEntityNotFoundResponse($annotation);
            }
        }
    }

    protected function attachUnauthenticatedResponse(AbstractAnnotation $annotation)
    {
        if (collect($annotation->responses)->where('response', 401)->count()) {
            return;
        }

        $resp = new Response([]);
        $resp->response = '401';
        $resp->ref = '#/components/responses/Unauthenticated';
        $annotation->responses[] = $resp;
    }

    protected function attachUnverifiedEmailResponse(AbstractAnnotation $annotation)
    {
        if (collect($annotation->responses)->where('response', 403)->count()) {
            return;
        }

        $resp = new Response([]);
        $resp->response = '403';
        $resp->ref = '#/components/responses/UnverifiedResponse';
        $annotation->responses[] = $resp;
    }

    protected function attachBadUuidResponse(AbstractAnnotation $annotation)
    {
        if (collect($annotation->responses)->where('response', 422)->count()) {
            return;
        }

        $resp = new Response([]);
        $resp->response = '422';
        $resp->ref = '#/components/responses/BadUuidResponse';
        $annotation->responses[] = $resp;
    }

    protected function attachEntityNotFoundResponse(AbstractAnnotation $annotation)
    {
        if (collect($annotation->responses)->where('response', 404)->count()) {
            return;
        }

        $resp = new Response([]);
        $resp->response = '404';
        $resp->ref = '#/components/responses/EntityNotFoundResponse';
        $annotation->responses[] = $resp;
    }


    protected function buildSchemaFromModel(Schema $schema)
    {
        $model = $this->getModelFromSchema($schema);

        if (!$model instanceof AbstractModel) {
            // d($schema->schema,$model);
            return;
        }

        $columns = $model->getTableColumnsInfo(true);
        $defaults = $model->getAttributes();

        $propertiesBag = &$schema->properties;
        $propertiesBag = $propertiesBag === UNDEFINED ? [] : $propertiesBag;

        $alreadyDescribedProperties = collect($propertiesBag)->pluck('property')->toArray();

        foreach ($columns as $columnName => $column) {
            if (in_array($columnName, $alreadyDescribedProperties)) {
                continue;
            }

            if (!$model->isFillable($columnName) && !$model->isVisible($columnName)) {
                continue;
            }

            $props = [
                'property' => $columnName,
            ];

            if ($model->isFillable($columnName) && !$model->isVisible($columnName)) {
                $props['writeOnly'] = true;
            } elseif (!$model->isFillable($columnName) && $model->isVisible($columnName)) {
                $props['readOnly'] = true;
            }

            if (array_key_exists($columnName, $defaults)) {
                $props['default'] = $defaults[$columnName];
            }

            $fieldType = $this->mapType($column->getType()->getName());
            $props = array_merge($props, $fieldType);

            $properties = new Property($props);
            $propertiesBag[] = $properties;
            $alreadyDescribedProperties[] = $columnName;

            // if ($model->is_nullable($columnName)) {
            //     $annotation->_context->nested->required = $annotation->_context->nested->required === UNDEFINED ? [] : $annotation->_context->nested->required;
            //     $annotation->_context->nested->required[] = $columnName;
            // }
        }
    }

    /**
     * Converts DB datatypes into swagger datatypes
     *
     * @param string $dbType
     *
     * @return array
     *
     * @see https://swagger.io/docs/specification/data-models/data-types/
     */
    private function mapType(string $dbType)
    {
        $mapping = [
            'decimal' => [
                'type' => 'number'
            ],
            'text' => [
                'type' => 'string'
            ],
            'guid' =>  [
                'type' => 'string',
                'format' => 'uuid',
            ],
            'datetime' =>  [
                'type' => 'string',
                'format' => 'date-time',
                'example' => \Carbon\Carbon::create('2021-03-27 14:32:26')->format('Y-m-d h:i:s'),
            ],
        ];

        return Arr::get($mapping, $dbType, ['type' => $dbType]);
    }

    protected function getModelFromSchema(Schema $schema)
    {
        $modelClassName = $this->getClassName($schema);

        if (!is_subclass_of($modelClassName, AbstractModel::class)) {
            return null;
        }

        /**
         * @var AbstractModel
         */
        $model = new $modelClassName;

        return $model;
    }

    private function makeOperationIdRedocCompatible(Analysis $analysis)
    {
        $allOperations = $analysis->getAnnotationsOfType(Operation::class);

        foreach ($allOperations as $operation) {
            if ($operation->operationId !== UNDEFINED) {
                continue;
            }
            $context = $operation->_context;
            if ($context && $context->method) {
                $source = $context->class ?? $context->interface ?? $context->trait;
                if ($source) {
                    if ($context->namespace) {
                        $operation->operationId = $context->namespace . '\\' . $source . '::' . $context->method;
                        $operation->operationId = str_replace('\\', '_', $operation->operationId);
                    } else {
                        $operation->operationId = $source . '::' . $context->method;
                    }
                } else {
                    $operation->operationId = $context->method;
                }
            }
        }
    }

    private function getAvailablePaths(Analysis $analysis)
    {
        $availablePaths = [];

        $availableMethods = [
            'get',
            'put',
            'post',
            'delete',
            'options',
            'head',
            'patch',
        ];

        foreach ($analysis->openapi->paths as $path) {
            // d($path);
            foreach ($availableMethods as $method) {
                if ($path->$method !== UNDEFINED) {
                    $availablePaths[$path->path . '/' . $method] = [
                        'method' => $method,
                        'pathItem' => $path,
                    ];
                }
            }
        }

        return $availablePaths;
    }

    private function registerRoutes(Analysis $analysis)
    {
        $routes = [];
        $paths = $this->getAvailablePaths($analysis);

        foreach ($paths as $actionPath => $data) {
            $method = $data['method'];
            $path = $data['pathItem'];
            $action = $path->$method->_context->method;

            if (empty($action)) {
                $action = Arr::get($path->$method->x, self::ROUTE_ACTION);

                if (empty($action)) {
                    $path->{strtolower($method)}->summary = '[ TODO: NOT IMPLEMENTED YET, but described in OpenAnnotation ]' . $path->{strtolower($method)}->summary;
                    // $path->description = 'NOT IMPLEMENTED YET';
                    continue;
                }
            }
            $controller = $this->getClassName($path->$method);
            $prefix = $this->getPathPrefix($path->$method);

            $route = [
                'prefix' => $prefix,
                'path' => $path->path,
                'method' => $method,
                'controller' => $controller,
                'action' => $action,
            ];

            if ($path->$method->x !== UNDEFINED) {
                if ($name = Arr::get($path->$method->x, self::ROUTE_PATH)) {
                    $route['name'] = $name;
                }
            }
            $route['middlewares'] = $this->getMiddlewares($path->$method);

            $routes[] = $route;
        }

        Storage::put('swagger/routes.json', json_encode($routes, JSON_PRETTY_PRINT));
    }

    private function getClassName(AbstractAnnotation $annotation)
    {
        return $annotation->_context->__get('namespace') . '\\' . $annotation->_context->class;
    }

    private function getPathPrefix(AbstractAnnotation $annotation)
    {
        $prefix = $annotation->_context->__get('namespace') === $this->api_controllers_prefix ? 'api/' . config('fpbx.api_version') : '';

        return $prefix;
    }

    private function getMiddlewares(AbstractAnnotation $annotation)
    {
        // if ($annotation->path === '/user') {
        //     d($annotation->path, $annotation->x);
        // }
        if ($middlewares = Arr::get($annotation->x, self::ROUTE_MIDDLEWARES, [])) {
            $middlewares = explode(',', $middlewares);
            $middlewares = array_map('trim', $middlewares);
            return $middlewares;
        }

        $isApi = $annotation->_context->__get('namespace') === $this->api_controllers_prefix;

        if ($isApi) {
            $auth = false;

            if ($annotation->security !== UNDEFINED) {
                foreach ($annotation->security as $security) {
                    if (array_key_exists('bearer_auth', $security)) {
                        $auth = true;
                        break;
                    }
                }
            }
            if ($auth) {
                // $middlewares = ['auth:api'];
                $middlewares = ['api', 'auth:sanctum', 'verified'];
            } else {
                $middlewares = ['api'];
            }
        } else {
            $middlewares = ['web'];
        }
        // if (!empty($route->middlewares)) {
        //     $middlewares = $route->middlewares;
        // } else {
        //     if ($route->auth) {
        //         $middlewares = ['auth:api'];
        //     } else {
        //         $middlewares = ['api'];
        //     }
        // }
        return $middlewares;
    }

    private function attachRepsonseExamples($actionPath, $data)
    {
        $responseExamples = $this->getResponseExamplesFromFiles($actionPath);

        if (empty($responseExamples)) {
            return;
        }

        $path = $data['pathItem'];
        $method = $data['method'];

        $responses = $path->$method->responses;
        if (UNDEFINED === $responses) {
            $responses = [];
        }
        $responses = array_merge($responses, $responseExamples);
        // return;
        $path->$method->responses = $responses;
        // d($path->$method, $responseExamples);
    }

    private function getResponseExamplesFromFiles($actionPath)
    {
        $directory = realpath($this->examplesPath . '/' . $actionPath . '/response');

        // $requestFiles = Storage::files($directory);
        if (!is_dir($directory)) {
            return [];
        }

        $responseDirectories = File::directories($directory);

        $responses = [];

        foreach ($responseDirectories as $directory) {
            $basename = basename($directory);
            list($code, $description) = explode(' ', $basename, 2);
            $responseFiles = File::files($directory);
            if (empty($responseFiles)) {
                continue;
            }

            $resp = new Response([]);
            $resp->response = $code;
            $resp->description = $description;
            $content = new MediaType([]);
            $content->mediaType = 'application/json';
            $content->examples = [];

            foreach ($responseFiles as $fileName) {
                $json = $fileName->getContents();
                $content->examples[] = new Examples([
                    'example' => basename($fileName, '.json'),
                    'summary' => basename($fileName, '.json'),
                    'value' => json_decode($json),
                ]);
            }
            $resp->content = [$content];

            $responses[] = $resp;
        }

        return $responses;
    }

    private function attachRequestExamples($actionPath, $data)
    {
        $requestExamples = $this->getRequestExamplesFromFiles($actionPath);

        if (empty($requestExamples)) {
            return;
        }

        $path = $data['pathItem'];
        $method = $data['method'];

        if (UNDEFINED === $path->$method->requestBody) {
            return;
        }

        $examples = $path->$method->requestBody->content[0]->examples;
        foreach ($requestExamples as $key => $value) {
            $requestExamples[$key] = new Examples($value);
        }

        if (!is_array($examples)) {
            $examples = [];
        }

        $examples = array_merge($examples, $requestExamples);

        $path->$method->requestBody->content[0]->examples = $examples;
    }

    private function getRequestExamplesFromFiles($actionPath)
    {
        $directory = realpath($this->examplesPath . '/' . $actionPath . '/request');

        // $requestFiles = Storage::files($directory);
        if (!is_dir($directory)) {
            return;
        }

        $requestFiles = File::files($directory);

        $examples = [];

        foreach ($requestFiles as $fileName) {
            $fileContents = $fileName->getContents();
            $fileContents = json_decode($fileContents);

            $json = [
                'example' => basename($fileName, '.json'),
                'summary' => basename($fileName, '.json'),
                'value' => $fileContents,
            ];

            $examples[] = $json;
        }

        return $examples;
    }
}
