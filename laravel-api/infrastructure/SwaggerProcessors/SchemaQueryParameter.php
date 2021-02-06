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
use OpenApi\Annotations\JsonContent;
use OpenApi\Annotations\RequestBody;
use Infrastructure\Database\Eloquent\Model;
use OpenApi\Annotations\AbstractAnnotation;

/**
 * Custom processor to translate the vendor tag `query-args-$ref` into query parameter annotations.
 *
 * Details for the parameters are taken from the referenced schema.
 */
class SchemaQueryParameter
{
    const X_QUERY_AGS_REF = 'query-args-$ref';
    const MODEL_INPUT_FIELDS = 'model-input-fields';

    public function __invoke(Analysis $analysis)
    {
        /**
         * @var Schema[]
         */
        $schemas = $analysis->getAnnotationsOfType(Schema::class, true);

        foreach ($schemas as $schema) {
            if ($schema->schema !== UNDEFINED) {
                $this->buildSchemaFromModel($schema);
            }
        }

        return;

        /**
         * @var RequestBody[]
         */
        $requests = $analysis->getAnnotationsOfType(RequestBody::class);
        foreach ($requests as $request) {
            $annotations = $request->_context->nested->_context->annotations;
            foreach ($annotations as $annotation) {
                if (
                    $annotation instanceof Schema &&
                    $annotation->ref !== UNDEFINED
                ) {
                    if ($schema = $this->schemaForRef($schemas, $annotation->ref)) {
                        $i = 0;
                        $type = null;
                        $startPoint = $annotation->_context->nested;
                        while (!$type) {
                            $i++;
                            if ($startPoint instanceof RequestBody) {
                                $type = 'request';
                                break;
                            }
                            if ($startPoint instanceof Response) {
                                $type = 'response';
                                break;
                            }

                            $startPoint = $startPoint->_context->nested;

                            if ($i > 100) { // JIC
                                break;
                            };
                        }

                        $this->expandModelSchema($annotation, $schema, $type);
                    }
                }
            }
        }
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
     * Expand the given operation by injecting parameters for all properties of the given schema.
     */
    protected function expandModelSchema(Schema $annotation, Schema $schema, $type)
    {

        $model = $this->getModelFromSchema($schema);

        if (!$model instanceof Model) {
            return;
        }

        // d($modelClassName . '||' . $type);

        $model->getVisible();

        $limitFields = false;

        switch ($type) {
            case 'request':
                $columns = $model->getTableColumnsInfo();
                $includeColumns = $model->getFillable();
                $limitFields = true;
                break;
            case 'response':
                $columns = $model->getTableColumnsInfo(true);
                $visible = $model->getVisible();
                $hidden = $model->getHidden();
                if (empty($visible) && empty($hidden)) {
                    $includeColumns = [];
                } elseif (!empty($visible)) {
                    $includeColumns = $visible;
                    $limitFields = true;
                } elseif (!empty($hidden)) {
                    $includeColumns = array_diff(array_keys($columns), $hidden);
                    $limitFields = true;
                }

                // d($includeColumns);
                break;
        }

        $propertiesBag = &$annotation->_context->nested->properties;

        $propertiesBag = $propertiesBag === UNDEFINED ? [] : $propertiesBag;
        $alreadyDescribedProperties = collect($propertiesBag)->pluck('property')->toArray();

        foreach ($columns as $columnName => $column) {
            if (in_array($columnName, $alreadyDescribedProperties)) {
                continue;
            }
            $props = [
                'property' => $columnName,
            ];

            // if ('response' === $type && $columnName === 'domain_uuid') {
            //     d($modelClassName, $columnName, $limitFields, $includeColumns);
            // }

            if ($limitFields && !in_array($columnName, $includeColumns)) {
                switch ($type) {
                    case 'request':
                        $props['readOnly'] = true;
                        break;

                    case 'response':
                        continue 2;
                        break;
                }
            }

            // if ('response' === $type && $columnName === 'domain_uuid') {
            //     d($columnName);
            // }

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
        ];

        return Arr::get($mapping, $dbType, ['type' => $dbType]);
    }

    /**
     * Find schema for the given ref.
     */
    protected function schemaForRef(array $schemas, string $ref)
    {
        foreach ($schemas as $schema) {
            if (Components::SCHEMA_REF . $schema->schema === $ref) {
                return $schema;
            }
        }

        return null;
    }

    protected function getModelFromSchema(Schema $schema)
    {
        $modelClassName = $schema->_context->__get('namespace') . '\\' . $schema->_context->class;

        if (!is_subclass_of($modelClassName, Model::class)) {
            return null;
        }

        /**
         * @var Model
         */
        $model = new $modelClassName;

        return $model;
    }
}
