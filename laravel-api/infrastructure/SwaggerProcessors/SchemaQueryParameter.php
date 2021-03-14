<?php

namespace Infrastructure\SwaggerProcessors;

use OpenApi\Analysis;
use Illuminate\Support\Arr;
use const OpenApi\UNDEFINED;
use OpenApi\Annotations\Items;
use OpenApi\Annotations\Schema;
use OpenApi\Annotations\Property;
use OpenApi\Annotations\Response;
use OpenApi\Annotations\MediaType;
use OpenApi\Annotations\Operation;
use OpenApi\Annotations\Parameter;
use OpenApi\Annotations\Components;
use OpenApi\Processors\OperationId;
use OpenApi\Annotations\JsonContent;
use OpenApi\Annotations\RequestBody;
use Illuminate\Support\Facades\Storage;
use Infrastructure\Database\Eloquent\AbstractModel;
use OpenApi\Annotations\AbstractAnnotation;

/**
 * Custom processor to translate the vendor tag `query-args-$ref` into query parameter annotations.
 *
 * Details for the parameters are taken from the referenced schema.
 */
class SchemaQueryParameter
{
    const MODEL_ADD_INCLUDES = 'model-add-includes';

    public function __invoke(Analysis $analysis)
    {
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
    }

    protected function buildSchemaFromModel(Schema $schema)
    {
        $model = $this->getModelFromSchema($schema);

        if (!$model instanceof Model) {
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
                'example' => \Carbon\Carbon::now()->format('Y-m-d h:i:s'),
            ],
        ];

        return Arr::get($mapping, $dbType, ['type' => $dbType]);
    }

    protected function getModelFromSchema(Schema $schema)
    {
        $modelClassName = $this->getClassName($schema);
        // d($modelClassName);

        if (!is_subclass_of($modelClassName, Model::class)) {
            return null;
        }

        /**
         * @var Model
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

    private function registerRoutes(Analysis $analysis)
    {
        // dd($analysis, $analysis->openapi->paths);
        $routes = [];
        $paths = $analysis->openapi->paths;
        // // d($paths[0], $paths[0]->_context->__get('parent')->method);
        // d();
        // dd('a');
        $availableMethods = [
            'get',
            'put',
            'post',
            'delete',
            'options',
            'head',
            'patch',
        ];
        foreach ($paths as $path) {
            foreach ($availableMethods as $method) {
                if ($path->$method !== UNDEFINED) {
                    $action = $path->_context->__get('method');
                    if (empty($action)) {

                        $path->{strtolower($method)}->summary = '[ TODO: NOT IMPLEMENTED YET, but described in OpenAnnotation ]' . $path->{strtolower($method)}->summary;
                        // $path->description = 'NOT IMPLEMENTED YET';
                        continue;
                    }
                    $controller = $this->getClassName($path);

                    $auth = false;
                    if ($path->$method->security !== UNDEFINED) {
                        foreach ($path->$method->security as $security) {
                            if (array_key_exists('bearer_auth', $security)) {
                                $auth = true;
                                break;
                            }
                        }
                    }

                    $routes[$path->path] = [
                        'auth' => $auth,
                        'method' => $method,
                        'controller' => $controller,
                        'action' => $action,
                    ];
                }
            }
        }

        Storage::disk('local')->put('swagger/routes.json', json_encode($routes, JSON_PRETTY_PRINT));
        // dd($routes);
    }

    private function getClassName(AbstractAnnotation $annotation) {
        return $annotation->_context->__get('namespace') . '\\' . $annotation->_context->class;
    }
}
