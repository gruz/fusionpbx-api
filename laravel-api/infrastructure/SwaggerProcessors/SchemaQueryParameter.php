<?php

namespace Infrastructure\SwaggerProcessors;

use OpenApi\Analysis;
use Illuminate\Support\Arr;
use const OpenApi\UNDEFINED;
use OpenApi\Annotations\Items;
use OpenApi\Annotations\Schema;
use OpenApi\Annotations\Property;
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
                        $this->expandModelSchema($annotation, $schema);
                    }
                }
            }
        }
    }

    /**
     * Expand the given operation by injecting parameters for all properties of the given schema.
     */
    protected function expandModelSchema(Schema $annotation, Schema $schema, $input = true)
    {
        $modelClassName = $schema->_context->__get('namespace') . '\\' . $schema->_context->class;

        /**
         * @var Model
         */
        $model = new $modelClassName;
        if (!$model instanceof Model) {
            return;
        }

        $columns = $model->getTableColumnsInfo();

        $fillable = $model->getFillable();

        $propertiesBag = &$annotation->_context->nested->properties;

        $propertiesBag = $propertiesBag === UNDEFINED ? [] : $propertiesBag;
        // $alreadyDescribedProperties = collect($propertiesBag)->pluck('property')->toArray();

        foreach ($columns as $columnName => $column) {
            $props = [
                'property' => $columnName,
            ];

            $type = $this->mapType($column->getType()->getName());
            $props = array_merge($props, $type);

            if (!in_array($columnName, $fillable)) {
                $props['readOnly'] = true;
            }

            $properties = new Property($props);
            $propertiesBag[] = $properties;

            // if ($model->is_nullable($columnName)) {
            //     $annotation->_context->nested->required = $annotation->_context->nested->required === UNDEFINED ? [] : $annotation->_context->nested->required;
            //     $annotation->_context->nested->required[] = $columnName;
            // }
        }
    }

    /**
     * Expand the given operation by injecting parameters for all properties of the given schema.
     */
    protected function expand(AbstractAnnotation $operation, Schema $schema)
    {
        $modelClassName = $schema->_context->__get('namespace') . '\\' . $schema->_context->class;

        /**
         * @var Model
         */
        $model = new $modelClassName;

        if (!$model instanceof Model) {
            return;
        }

        $columns = $model->getTableColumnsInfo();

        $fillable = $model->getFillable();

        foreach ($columns as $colmunName => $columnData) {
            if (!in_array($colmunName, $fillable)) {
                unset($columns[$colmunName]);
            }
        }

        // d($schema->schema, $columns);

        // d($operation);

        $operation->properties = $operation->properties === UNDEFINED ? [] : $operation->properties;

        foreach ($columns as $columnName => $column) {
            $type = $this->mapType($column->getType()->getName());
            $properties = new Property([
                'property' => $columnName,
                // 'in' => 'query',
                // 'required' => false,
                'type' => $type,
                'schema' => [
                    'type' => 'integer',
                    'format' => 'int64',
                ]
            ]);
            $operation->properties[] = $properties;
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

        return Arr::get($mapping, $dbType, [ 'type' => $dbType ] );
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
}
