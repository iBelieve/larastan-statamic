<?php

declare(strict_types=1);

namespace IBelieve\LarastanStatamic\Types;

use IBelieve\LarastanStatamic\Blueprints\FieldDefinition;
use PHPStan\Type\ArrayType;
use PHPStan\Type\BooleanType;
use PHPStan\Type\FloatType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\MixedType;
use PHPStan\Type\NullType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;

final class FieldtypeMapper
{
    /** @var array<string, string> */
    private const STRING_TYPES = [
        'text' => 'string',
        'textarea' => 'string',
        'markdown' => 'string',
        'slug' => 'string',
        'code' => 'string',
        'color' => 'string',
        'html' => 'string',
        'video' => 'string',
        'template' => 'string',
        'link' => 'string',
        'time' => 'string',
        'yaml' => 'string',
        'table' => 'string',
    ];

    /** @var array<string, string> */
    private const RELATIONSHIP_TYPES = [
        'entries' => 'Statamic\Entries\Entry',
        'terms' => 'Statamic\Taxonomies\LocalizedTerm',
        'assets' => 'Statamic\Assets\Asset',
        'users' => 'Statamic\Contracts\Auth\User',
    ];

    /** @var array<string, string> */
    private array $customFieldtypes;

    /**
     * @param array<string, string> $customFieldtypes
     */
    public function __construct(array $customFieldtypes = [])
    {
        $this->customFieldtypes = $customFieldtypes;
    }

    public function mapToType(FieldDefinition $field): Type
    {
        $fieldtype = $field->type;

        // String types
        if (isset(self::STRING_TYPES[$fieldtype])) {
            return new StringType();
        }

        // Boolean
        if ($fieldtype === 'toggle') {
            return new BooleanType();
        }

        // Integer types
        if ($fieldtype === 'integer' || $fieldtype === 'range') {
            return new IntegerType();
        }

        // Float
        if ($fieldtype === 'float') {
            return new FloatType();
        }

        // Date → Carbon
        if ($fieldtype === 'date') {
            return new ObjectType('Carbon\Carbon');
        }

        // Relationship types (entries, terms, assets, users)
        if (isset(self::RELATIONSHIP_TYPES[$fieldtype])) {
            return $this->mapRelationshipType($field, self::RELATIONSHIP_TYPES[$fieldtype]);
        }

        // Select-like (can be string or int)
        if (in_array($fieldtype, ['select', 'button_group', 'radio'], true)) {
            return new UnionType([new StringType(), new IntegerType()]);
        }

        // Array types
        if (in_array($fieldtype, ['checkboxes', 'array', 'list', 'bard', 'replicator', 'grid', 'structures'], true)) {
            return new ArrayType(new MixedType(), new MixedType());
        }

        // Reference types that return strings
        if (in_array($fieldtype, ['collections', 'taxonomies', 'sites', 'form'], true)) {
            return new StringType();
        }

        // Custom fieldtype overrides
        if (isset($this->customFieldtypes[$fieldtype])) {
            return new ObjectType($this->customFieldtypes[$fieldtype]);
        }

        // Unknown fieldtype → mixed
        return new MixedType();
    }

    private function mapRelationshipType(FieldDefinition $field, string $className): Type
    {
        $objectType = new ObjectType($className);

        if ($field->isSingleRelationship()) {
            return new UnionType([$objectType, new NullType()]);
        }

        // Multiple items: returns an array of objects
        return new ArrayType(new IntegerType(), $objectType);
    }
}
