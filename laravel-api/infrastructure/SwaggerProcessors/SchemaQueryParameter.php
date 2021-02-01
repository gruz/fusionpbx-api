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
                    // !$annotation instanceof Property &&
                    $annotation->ref !== UNDEFINED
                ) {

                    if ($schema = $this->schemaForRef($schemas, $annotation->ref)) {
                        // d($annotation);
                        $this->expandModelSchema($annotation, $schema);
                        // $this->cleanUp($annotation);
                    }
                }
            }
        }
        return;

        // foreach ($schemas as $key => $schema) {
        //     if ($schema->_context->extends !== 'Model') {
        //         continue;
        //     }
        //     // $this->expandModelSchema($schema);
        //     d($schema, $schema->schema, $schema->ref, $schema->x);

        // }
        // $operations = $analysis->getAnnotationsOfType(MediaType::class);
        $operations = $analysis->getAnnotationsOfType(JsonContent::class);

        // return;
        // $operations = $analysis->getAnnotationsOfType(JsonContent::class);

        foreach ($operations as $operation) {
            if ($operation->x !== UNDEFINED && array_key_exists(self::MODEL_INPUT_FIELDS, $operation->x)) {
                foreach ($operation->x[self::MODEL_INPUT_FIELDS] as $schemaPath) {
                    if ($schema = $this->schemaForRef($schemas, $schemaPath)) {
                        $this->expand($operation, $schema);
                        $this->cleanUp($operation);
                    }
                }
            }
        }

        return;

        $operations = $analysis->getAnnotationsOfType(Operation::class);

        foreach ($operations as $operation) {
            if ($operation->x !== UNDEFINED && array_key_exists(self::X_QUERY_AGS_REF, $operation->x)) {
                if ($schema = $this->schemaForRef($schemas, $operation->x[self::X_QUERY_AGS_REF])) {
                    $this->expandQueryArgs($operation, $schema);
                    $this->cleanUp($operation);
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

        // foreach ($columns as $colmunName => $columnData) {
        //     if (!in_array($colmunName, $fillable)) {
        //         unset($columns[$colmunName]);
        //     }
        // }

        $propertiesBag = &$annotation->_context->nested->properties;

        $propertiesBag = $propertiesBag === UNDEFINED ? [] : $propertiesBag;
        $alreadyDescribedProperties = collect($propertiesBag)->pluck('property')->toArray();
        // dd($alreadyDescribedProperties);

        // d($schema->properties, $availabeProperties);
        // return;
        foreach ($columns as $columnName => $column) {
            $props = [
                'property' => $columnName,
                // 'in' => 'query',
                // 'required' => false,
                // 'type' => $type,
                // 'schema' => [
                //     'type' => 'integer',
                //     'format' => 'int64',
                // ]
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
        // d($operation);


        return;

        $operation->parameters = $operation->parameters === UNDEFINED ? [] : $operation->parameters;
        foreach ($schema->properties as $property) {
            $parameter = new Parameter([
                'name' => $property->property,
                'in' => 'query',
                'required' => false,
                'schema' => [
                    'type' => 'integer',
                    'format' => 'int64',
                ]
            ]);
            $operation->parameters[] = $parameter;
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
            'text' => [
                'type' => 'string'
            ],
            'guid' =>  [
                'type' => 'string',
                'format' => 'uuid',
            ]
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

    /**
     * Expand the given operation by injecting parameters for all properties of the given schema.
     */
    protected function expandQueryArgs(Operation $operation, Schema $schema)
    {
        if ($schema->properties === UNDEFINED || !$schema->properties) {
            return;
        }

        $operation->parameters = $operation->parameters === UNDEFINED ? [] : $operation->parameters;
        foreach ($schema->properties as $property) {
            $parameter = new Parameter([
                'name' => $property->property,
                'in' => 'query',
                'required' => false,
                'schema' => [
                    'type' => 'integer',
                    'format' => 'int64',
                ]
            ]);
            $operation->parameters[] = $parameter;
        }
    }

    /**
     * Clean up.
     */
    protected function cleanUp($operation)
    {
        unset($operation->x[self::X_QUERY_AGS_REF]);
        if (!$operation->x) {
            $operation->x = UNDEFINED;
        }
    }
}
