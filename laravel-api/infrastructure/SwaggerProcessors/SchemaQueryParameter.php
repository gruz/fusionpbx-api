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
    const MODEL_ADD_INCLUDES = 'model-add-includes';

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
        ];

        return Arr::get($mapping, $dbType, ['type' => $dbType]);
    }

    protected function getModelFromSchema(Schema $schema)
    {
        $modelClassName = $schema->_context->__get('namespace') . '\\' . $schema->_context->class;
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
}
