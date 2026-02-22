<?php

declare(strict_types=1);

namespace IBelieve\LarastanStatamic\Tests\Properties;

use IBelieve\LarastanStatamic\Blueprints\BlueprintLocator;
use IBelieve\LarastanStatamic\Blueprints\BlueprintParser;
use IBelieve\LarastanStatamic\Blueprints\BlueprintRepository;
use IBelieve\LarastanStatamic\Properties\BlueprintPropertyExtension;
use IBelieve\LarastanStatamic\Types\FieldtypeMapper;
use PHPStan\Testing\PHPStanTestCase;
use PHPStan\Type\BooleanType;
use PHPStan\Type\StringType;

final class BlueprintPropertyExtensionTest extends PHPStanTestCase
{
    private BlueprintPropertyExtension $extension;

    protected function setUp(): void
    {
        $locator = new BlueprintLocator([
            __DIR__.'/../Fixtures/blueprints',
        ]);
        $parser = new BlueprintParser;
        $repository = new BlueprintRepository($locator, $parser);
        $fieldtypeMapper = new FieldtypeMapper;

        $this->extension = new BlueprintPropertyExtension($repository, $fieldtypeMapper);
    }

    /**
     * @return string[]
     */
    public static function getAdditionalConfigFiles(): array
    {
        return [
            __DIR__.'/../Fixtures/Rules/rules-test.neon',
        ];
    }

    public function test_has_property_for_blueprint_field(): void
    {
        $reflectionProvider = self::createReflectionProvider();
        $classReflection = $reflectionProvider->getClass('Statamic\Entries\Entry');

        $this->assertTrue($this->extension->hasProperty($classReflection, 'body'));
    }

    public function test_has_property_for_universal_field(): void
    {
        $reflectionProvider = self::createReflectionProvider();
        $classReflection = $reflectionProvider->getClass('Statamic\Entries\Entry');

        $this->assertTrue($this->extension->hasProperty($classReflection, 'title'));
    }

    public function test_does_not_have_nonexistent_property(): void
    {
        $reflectionProvider = self::createReflectionProvider();
        $classReflection = $reflectionProvider->getClass('Statamic\Entries\Entry');

        $this->assertFalse($this->extension->hasProperty($classReflection, 'nonexistent_field'));
    }

    public function test_does_not_apply_to_non_content_classes(): void
    {
        $reflectionProvider = self::createReflectionProvider();
        $classReflection = $reflectionProvider->getClass('stdClass');

        $this->assertFalse($this->extension->hasProperty($classReflection, 'body'));
    }

    public function test_get_property_returns_correct_type_for_blueprint_field(): void
    {
        $reflectionProvider = self::createReflectionProvider();
        $classReflection = $reflectionProvider->getClass('Statamic\Entries\Entry');

        $property = $this->extension->getProperty($classReflection, 'body');

        $this->assertInstanceOf(StringType::class, $property->getReadableType());
    }

    public function test_get_property_returns_correct_type_for_toggle(): void
    {
        $reflectionProvider = self::createReflectionProvider();
        $classReflection = $reflectionProvider->getClass('Statamic\Entries\Entry');

        $property = $this->extension->getProperty($classReflection, 'featured');

        $this->assertInstanceOf(BooleanType::class, $property->getReadableType());
    }

    public function test_get_property_returns_correct_type_for_universal_field(): void
    {
        $reflectionProvider = self::createReflectionProvider();
        $classReflection = $reflectionProvider->getClass('Statamic\Entries\Entry');

        $property = $this->extension->getProperty($classReflection, 'title');

        $this->assertInstanceOf(StringType::class, $property->getReadableType());
    }

    public function test_property_is_readable_and_writable(): void
    {
        $reflectionProvider = self::createReflectionProvider();
        $classReflection = $reflectionProvider->getClass('Statamic\Entries\Entry');

        $property = $this->extension->getProperty($classReflection, 'body');

        $this->assertTrue($property->isReadable());
        $this->assertTrue($property->isWritable());
        $this->assertTrue($property->isPublic());
        $this->assertFalse($property->isPrivate());
        $this->assertFalse($property->isStatic());
    }

    public function test_has_property_for_global_variables(): void
    {
        $reflectionProvider = self::createReflectionProvider();
        $classReflection = $reflectionProvider->getClass('Statamic\Globals\Variables');

        $this->assertTrue($this->extension->hasProperty($classReflection, 'site_name'));
        $this->assertTrue($this->extension->hasProperty($classReflection, 'handle'));
        $this->assertFalse($this->extension->hasProperty($classReflection, 'nonexistent'));
    }

    public function test_has_property_for_term(): void
    {
        $reflectionProvider = self::createReflectionProvider();
        $classReflection = $reflectionProvider->getClass('Statamic\Taxonomies\LocalizedTerm');

        $this->assertTrue($this->extension->hasProperty($classReflection, 'description'));
        $this->assertTrue($this->extension->hasProperty($classReflection, 'title'));
        $this->assertFalse($this->extension->hasProperty($classReflection, 'nonexistent'));
    }

    public function test_has_property_for_asset(): void
    {
        $reflectionProvider = self::createReflectionProvider();
        $classReflection = $reflectionProvider->getClass('Statamic\Assets\Asset');

        $this->assertTrue($this->extension->hasProperty($classReflection, 'alt'));
        $this->assertTrue($this->extension->hasProperty($classReflection, 'path'));
        $this->assertFalse($this->extension->hasProperty($classReflection, 'nonexistent'));
    }

    public function test_works_with_contract_interfaces(): void
    {
        $reflectionProvider = self::createReflectionProvider();
        $classReflection = $reflectionProvider->getClass('Statamic\Contracts\Entries\Entry');

        $this->assertTrue($this->extension->hasProperty($classReflection, 'body'));
        $this->assertTrue($this->extension->hasProperty($classReflection, 'title'));
    }
}
