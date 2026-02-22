<?php

declare(strict_types=1);

namespace IBelieve\LarastanStatamic\Tests\Support;

use IBelieve\LarastanStatamic\Support\ContentClassMap;
use PHPStan\Testing\PHPStanTestCase;
use PHPStan\Type\BooleanType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\StringType;
use PHPStan\Type\UnionType;

final class ContentClassMapTest extends PHPStanTestCase
{
    /**
     * @return string[]
     */
    public static function getAdditionalConfigFiles(): array
    {
        return [
            __DIR__.'/../Fixtures/Rules/rules-test.neon',
        ];
    }

    public function test_maps_entry_class(): void
    {
        $reflectionProvider = self::createReflectionProvider();
        $classReflection = $reflectionProvider->getClass('Statamic\Entries\Entry');

        $this->assertSame('entry', ContentClassMap::getContentType($classReflection));
    }

    public function test_maps_entry_contract(): void
    {
        $reflectionProvider = self::createReflectionProvider();
        $classReflection = $reflectionProvider->getClass('Statamic\Contracts\Entries\Entry');

        $this->assertSame('entry', ContentClassMap::getContentType($classReflection));
    }

    public function test_maps_term_class(): void
    {
        $reflectionProvider = self::createReflectionProvider();
        $classReflection = $reflectionProvider->getClass('Statamic\Taxonomies\LocalizedTerm');

        $this->assertSame('term', ContentClassMap::getContentType($classReflection));
    }

    public function test_maps_term_contract(): void
    {
        $reflectionProvider = self::createReflectionProvider();
        $classReflection = $reflectionProvider->getClass('Statamic\Contracts\Taxonomies\Term');

        $this->assertSame('term', ContentClassMap::getContentType($classReflection));
    }

    public function test_maps_asset_class(): void
    {
        $reflectionProvider = self::createReflectionProvider();
        $classReflection = $reflectionProvider->getClass('Statamic\Assets\Asset');

        $this->assertSame('asset', ContentClassMap::getContentType($classReflection));
    }

    public function test_maps_asset_contract(): void
    {
        $reflectionProvider = self::createReflectionProvider();
        $classReflection = $reflectionProvider->getClass('Statamic\Contracts\Assets\Asset');

        $this->assertSame('asset', ContentClassMap::getContentType($classReflection));
    }

    public function test_maps_global_variables_class(): void
    {
        $reflectionProvider = self::createReflectionProvider();
        $classReflection = $reflectionProvider->getClass('Statamic\Globals\Variables');

        $this->assertSame('global', ContentClassMap::getContentType($classReflection));
    }

    public function test_maps_global_variables_contract(): void
    {
        $reflectionProvider = self::createReflectionProvider();
        $classReflection = $reflectionProvider->getClass('Statamic\Contracts\Globals\Variables');

        $this->assertSame('global', ContentClassMap::getContentType($classReflection));
    }

    public function test_returns_null_for_non_content_class(): void
    {
        $reflectionProvider = self::createReflectionProvider();
        $classReflection = $reflectionProvider->getClass('stdClass');

        $this->assertNull(ContentClassMap::getContentType($classReflection));
    }

    public function test_is_content_class(): void
    {
        $reflectionProvider = self::createReflectionProvider();

        $this->assertTrue(ContentClassMap::isContentClass(
            $reflectionProvider->getClass('Statamic\Entries\Entry'),
        ));
        $this->assertFalse(ContentClassMap::isContentClass(
            $reflectionProvider->getClass('stdClass'),
        ));
    }

    public function test_entry_universal_properties(): void
    {
        $props = ContentClassMap::getUniversalProperties('entry');

        $this->assertArrayHasKey('id', $props);
        $this->assertArrayHasKey('title', $props);
        $this->assertArrayHasKey('slug', $props);
        $this->assertArrayHasKey('url', $props);
        $this->assertArrayHasKey('published', $props);
        $this->assertArrayHasKey('date', $props);
        $this->assertArrayHasKey('collection', $props);

        $this->assertInstanceOf(StringType::class, $props['id']);
        $this->assertInstanceOf(StringType::class, $props['title']);
        $this->assertInstanceOf(BooleanType::class, $props['published']);
        $this->assertInstanceOf(ObjectType::class, $props['date']);
        $this->assertInstanceOf(ObjectType::class, $props['collection']);
    }

    public function test_asset_universal_properties(): void
    {
        $props = ContentClassMap::getUniversalProperties('asset');

        $this->assertArrayHasKey('path', $props);
        $this->assertArrayHasKey('filename', $props);
        $this->assertArrayHasKey('extension', $props);
        $this->assertArrayHasKey('size', $props);
        $this->assertArrayHasKey('is_image', $props);

        $this->assertInstanceOf(StringType::class, $props['path']);
        $this->assertInstanceOf(IntegerType::class, $props['size']);
        $this->assertInstanceOf(BooleanType::class, $props['is_image']);
    }

    public function test_term_universal_properties(): void
    {
        $props = ContentClassMap::getUniversalProperties('term');

        $this->assertArrayHasKey('id', $props);
        $this->assertArrayHasKey('title', $props);
        $this->assertArrayHasKey('slug', $props);
        $this->assertArrayHasKey('url', $props);

        $this->assertInstanceOf(StringType::class, $props['title']);
    }

    public function test_global_universal_properties(): void
    {
        $props = ContentClassMap::getUniversalProperties('global');

        $this->assertArrayHasKey('id', $props);
        $this->assertArrayHasKey('handle', $props);
        $this->assertArrayHasKey('title', $props);
        $this->assertArrayHasKey('locale', $props);

        $this->assertInstanceOf(StringType::class, $props['handle']);
    }

    public function test_nullable_universal_properties(): void
    {
        $entryProps = ContentClassMap::getUniversalProperties('entry');

        $this->assertInstanceOf(UnionType::class, $entryProps['uri']);
        $this->assertInstanceOf(UnionType::class, $entryProps['api_url']);
    }

    public function test_unknown_content_type_returns_empty(): void
    {
        $props = ContentClassMap::getUniversalProperties('unknown');

        $this->assertSame([], $props);
    }
}
