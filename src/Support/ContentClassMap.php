<?php

declare(strict_types=1);

namespace IBelieve\LarastanStatamic\Support;

use PHPStan\Reflection\ClassReflection;
use PHPStan\Type\BooleanType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\NullType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;

/**
 * @phpstan-import-type ContentType from \IBelieve\LarastanStatamic\Blueprints\BlueprintLocator
 */
final class ContentClassMap
{
    /** @var array<string, ContentType> */
    private const CLASS_TO_CONTENT_TYPE = [
        'Statamic\Entries\Entry' => 'entry',
        'Statamic\Contracts\Entries\Entry' => 'entry',
        'Statamic\Taxonomies\LocalizedTerm' => 'term',
        'Statamic\Contracts\Taxonomies\Term' => 'term',
        'Statamic\Assets\Asset' => 'asset',
        'Statamic\Contracts\Assets\Asset' => 'asset',
        'Statamic\Globals\Variables' => 'global',
        'Statamic\Contracts\Globals\Variables' => 'global',
    ];

    /** @var array<ContentType, array<string, Type>> */
    private static ?array $universalProperties = null;

    /**
     * @return ContentType|null
     */
    public static function getContentType(ClassReflection $classReflection): ?string
    {
        $className = $classReflection->getName();

        if (isset(self::CLASS_TO_CONTENT_TYPE[$className])) {
            return self::CLASS_TO_CONTENT_TYPE[$className];
        }

        // Check parent classes and interfaces
        foreach ($classReflection->getAncestors() as $ancestor) {
            $ancestorName = $ancestor->getName();

            if (isset(self::CLASS_TO_CONTENT_TYPE[$ancestorName])) {
                return self::CLASS_TO_CONTENT_TYPE[$ancestorName];
            }
        }

        return null;
    }

    public static function isContentClass(ClassReflection $classReflection): bool
    {
        return self::getContentType($classReflection) !== null;
    }

    /**
     * @param  ContentType  $contentType
     * @return array<string, Type>
     */
    public static function getUniversalProperties(string $contentType): array
    {
        if (self::$universalProperties === null) {
            self::$universalProperties = self::buildUniversalProperties();
        }

        return self::$universalProperties[$contentType] ?? [];
    }

    /**
     * @return array<ContentType, array<string, Type>>
     */
    private static function buildUniversalProperties(): array
    {
        $carbonType = new ObjectType('Carbon\Carbon');
        $stringOrNull = new UnionType([new StringType, new NullType]);

        return [
            'entry' => [
                'id' => new StringType,
                'title' => new StringType,
                'slug' => new StringType,
                'url' => new StringType,
                'uri' => $stringOrNull,
                'permalink' => new StringType,
                'date' => $carbonType,
                'published' => new BooleanType,
                'status' => new StringType,
                'locale' => new StringType,
                'last_modified' => $carbonType,
                'edit_url' => new StringType,
                'api_url' => $stringOrNull,
                'collection' => new ObjectType('Statamic\Entries\Collection'),
            ],
            'term' => [
                'id' => new StringType,
                'title' => new StringType,
                'slug' => new StringType,
                'url' => new StringType,
                'uri' => $stringOrNull,
                'permalink' => new StringType,
                'locale' => new StringType,
                'edit_url' => new StringType,
                'api_url' => $stringOrNull,
            ],
            'asset' => [
                'id' => new StringType,
                'url' => new StringType,
                'permalink' => new StringType,
                'path' => new StringType,
                'filename' => new StringType,
                'basename' => new StringType,
                'extension' => new StringType,
                'size' => new IntegerType,
                'size_bytes' => new IntegerType,
                'last_modified' => $carbonType,
                'mime_type' => new StringType,
                'edit_url' => new StringType,
                'api_url' => $stringOrNull,
                'is_image' => new BooleanType,
                'is_audio' => new BooleanType,
                'is_video' => new BooleanType,
                'width' => new UnionType([new IntegerType, new NullType]),
                'height' => new UnionType([new IntegerType, new NullType]),
            ],
            'global' => [
                'id' => new StringType,
                'handle' => new StringType,
                'title' => new StringType,
                'locale' => new StringType,
                'api_url' => $stringOrNull,
            ],
        ];
    }
}
