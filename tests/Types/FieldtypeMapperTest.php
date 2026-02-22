<?php

declare(strict_types=1);

namespace IBelieve\LarastanStatamic\Tests\Types;

use IBelieve\LarastanStatamic\Blueprints\FieldDefinition;
use IBelieve\LarastanStatamic\Types\FieldtypeMapper;
use PHPStan\Type\ArrayType;
use PHPStan\Type\BooleanType;
use PHPStan\Type\FloatType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\StringType;
use PHPStan\Type\UnionType;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class FieldtypeMapperTest extends TestCase
{
    private FieldtypeMapper $mapper;

    protected function setUp(): void
    {
        $this->mapper = new FieldtypeMapper;
    }

    #[DataProvider('stringTypeProvider')]
    public function test_maps_string_types(string $fieldtype): void
    {
        $field = new FieldDefinition($fieldtype, $fieldtype);

        $type = $this->mapper->mapToType($field);

        $this->assertInstanceOf(StringType::class, $type);
    }

    /**
     * @return iterable<string, list<string>>
     */
    public static function stringTypeProvider(): iterable
    {
        yield 'text' => ['text'];
        yield 'textarea' => ['textarea'];
        yield 'markdown' => ['markdown'];
        yield 'slug' => ['slug'];
        yield 'code' => ['code'];
        yield 'color' => ['color'];
        yield 'html' => ['html'];
        yield 'video' => ['video'];
        yield 'template' => ['template'];
        yield 'link' => ['link'];
        yield 'time' => ['time'];
        yield 'yaml' => ['yaml'];
    }

    public function test_maps_toggle_to_boolean(): void
    {
        $field = new FieldDefinition('toggle', 'toggle');

        $type = $this->mapper->mapToType($field);

        $this->assertInstanceOf(BooleanType::class, $type);
    }

    #[DataProvider('integerTypeProvider')]
    public function test_maps_integer_types(string $fieldtype): void
    {
        $field = new FieldDefinition($fieldtype, $fieldtype);

        $type = $this->mapper->mapToType($field);

        $this->assertInstanceOf(IntegerType::class, $type);
    }

    /**
     * @return iterable<string, list<string>>
     */
    public static function integerTypeProvider(): iterable
    {
        yield 'integer' => ['integer'];
        yield 'range' => ['range'];
    }

    public function test_maps_float(): void
    {
        $field = new FieldDefinition('float', 'float');

        $type = $this->mapper->mapToType($field);

        $this->assertInstanceOf(FloatType::class, $type);
    }

    public function test_maps_date_to_carbon(): void
    {
        $field = new FieldDefinition('date', 'date');

        $type = $this->mapper->mapToType($field);

        $this->assertInstanceOf(ObjectType::class, $type);
        $this->assertSame('Carbon\Carbon', $type->getClassName());
    }

    public function test_maps_entries_with_max_items_1(): void
    {
        $field = new FieldDefinition('related', 'entries', ['max_items' => 1]);

        $type = $this->mapper->mapToType($field);

        $this->assertInstanceOf(UnionType::class, $type);

        // Should be Entry|null
        $innerTypes = $type->getTypes();
        $classNames = [];

        foreach ($innerTypes as $innerType) {
            if ($innerType instanceof ObjectType) {
                $classNames[] = $innerType->getClassName();
            }
        }

        $this->assertContains('Statamic\Entries\Entry', $classNames);
    }

    public function test_maps_entries_without_max_items(): void
    {
        $field = new FieldDefinition('related', 'entries');

        $type = $this->mapper->mapToType($field);

        $this->assertInstanceOf(ArrayType::class, $type);
    }

    public function test_maps_assets_with_max_items_1(): void
    {
        $field = new FieldDefinition('image', 'assets', ['max_items' => 1]);

        $type = $this->mapper->mapToType($field);

        $this->assertInstanceOf(UnionType::class, $type);
    }

    public function test_maps_assets_without_max_items(): void
    {
        $field = new FieldDefinition('images', 'assets');

        $type = $this->mapper->mapToType($field);

        $this->assertInstanceOf(ArrayType::class, $type);
    }

    public function test_maps_users_with_max_items_1(): void
    {
        $field = new FieldDefinition('author', 'users', ['max_items' => 1]);

        $type = $this->mapper->mapToType($field);

        $this->assertInstanceOf(UnionType::class, $type);
    }

    public function test_maps_terms_without_max_items(): void
    {
        $field = new FieldDefinition('tags', 'terms');

        $type = $this->mapper->mapToType($field);

        $this->assertInstanceOf(ArrayType::class, $type);
    }

    #[DataProvider('selectTypeProvider')]
    public function test_maps_select_types(string $fieldtype): void
    {
        $field = new FieldDefinition($fieldtype, $fieldtype);

        $type = $this->mapper->mapToType($field);

        $this->assertInstanceOf(UnionType::class, $type);
    }

    /**
     * @return iterable<string, list<string>>
     */
    public static function selectTypeProvider(): iterable
    {
        yield 'select' => ['select'];
        yield 'button_group' => ['button_group'];
        yield 'radio' => ['radio'];
    }

    #[DataProvider('arrayTypeProvider')]
    public function test_maps_array_types(string $fieldtype): void
    {
        $field = new FieldDefinition($fieldtype, $fieldtype);

        $type = $this->mapper->mapToType($field);

        $this->assertInstanceOf(ArrayType::class, $type);
    }

    /**
     * @return iterable<string, list<string>>
     */
    public static function arrayTypeProvider(): iterable
    {
        yield 'checkboxes' => ['checkboxes'];
        yield 'array' => ['array'];
        yield 'list' => ['list'];
        yield 'bard' => ['bard'];
        yield 'replicator' => ['replicator'];
        yield 'grid' => ['grid'];
    }

    public function test_maps_unknown_to_mixed(): void
    {
        $field = new FieldDefinition('unknown', 'some_custom_type');

        $type = $this->mapper->mapToType($field);

        $this->assertInstanceOf(MixedType::class, $type);
    }

    public function test_maps_computed_to_mixed(): void
    {
        $field = new FieldDefinition('brand_slug', 'computed');

        $type = $this->mapper->mapToType($field);

        $this->assertInstanceOf(MixedType::class, $type);
    }

    public function test_custom_fieldtype_mapping(): void
    {
        $mapper = new FieldtypeMapper(['my_type' => 'App\MyType']);
        $field = new FieldDefinition('custom', 'my_type');

        $type = $mapper->mapToType($field);

        $this->assertInstanceOf(ObjectType::class, $type);
        $this->assertSame('App\MyType', $type->getClassName());
    }
}
