<?php

declare(strict_types=1);

namespace IBelieve\LarastanStatamic\Tests\Blueprints;

use IBelieve\LarastanStatamic\Blueprints\BlueprintLocator;
use IBelieve\LarastanStatamic\Blueprints\BlueprintParser;
use IBelieve\LarastanStatamic\Blueprints\BlueprintRepository;
use IBelieve\LarastanStatamic\Computed\ComputedFieldScanner;
use PHPUnit\Framework\TestCase;

final class BlueprintRepositoryTest extends TestCase
{
    private BlueprintRepository $repository;

    protected function setUp(): void
    {
        $locator = new BlueprintLocator([
            __DIR__.'/../Fixtures/blueprints',
        ]);
        $parser = new BlueprintParser;
        $scanner = new ComputedFieldScanner([]);
        $this->repository = new BlueprintRepository($locator, $parser, $scanner);
    }

    public function test_gets_entry_fields(): void
    {
        $fields = $this->repository->getFieldsForContentType('entry');

        $this->assertNotEmpty($fields);

        $handles = array_map(fn ($f) => $f->handle, $fields);

        $this->assertContains('body', $handles);
        $this->assertContains('featured', $handles);
    }

    public function test_gets_term_fields(): void
    {
        $fields = $this->repository->getFieldsForContentType('term');

        $this->assertNotEmpty($fields);

        $handles = array_map(fn ($f) => $f->handle, $fields);

        $this->assertContains('description', $handles);
    }

    public function test_gets_global_fields(): void
    {
        $fields = $this->repository->getFieldsForContentType('global');

        $handles = array_map(fn ($f) => $f->handle, $fields);

        $this->assertContains('site_name', $handles);
        $this->assertContains('maintenance_mode', $handles);
    }

    public function test_gets_asset_fields(): void
    {
        $fields = $this->repository->getFieldsForContentType('asset');

        $handles = array_map(fn ($f) => $f->handle, $fields);

        $this->assertContains('alt', $handles);
        $this->assertContains('caption', $handles);
    }

    public function test_has_field(): void
    {
        $this->assertTrue($this->repository->hasField('entry', 'body'));
        $this->assertTrue($this->repository->hasField('entry', 'featured'));
        $this->assertFalse($this->repository->hasField('entry', 'nonexistent'));
    }

    public function test_get_field(): void
    {
        $field = $this->repository->getField('entry', 'body');

        $this->assertNotNull($field);
        $this->assertSame('body', $field->handle);
        $this->assertSame('markdown', $field->type);
    }

    public function test_get_field_returns_null_for_missing(): void
    {
        $field = $this->repository->getField('entry', 'nonexistent');

        $this->assertNull($field);
    }

    public function test_field_map_deduplicates_by_handle(): void
    {
        $map = $this->repository->getFieldMapForContentType('entry');

        $this->assertArrayHasKey('body', $map);
        $this->assertSame('markdown', $map['body']->type);
    }

    public function test_caches_results(): void
    {
        // Call twice to exercise the caching branch
        $fields1 = $this->repository->getFieldsForContentType('entry');
        $fields2 = $this->repository->getFieldsForContentType('entry');

        $this->assertSame(count($fields1), count($fields2));
    }

    public function test_includes_computed_fields_for_entries(): void
    {
        $repository = $this->repositoryWithComputedFields();

        $this->assertTrue($repository->hasField('entry', 'brand_slug'));
        $this->assertTrue($repository->hasField('entry', 'shares'));
        $this->assertTrue($repository->hasField('entry', 'likes'));
    }

    public function test_computed_fields_not_added_to_non_entry_types(): void
    {
        $repository = $this->repositoryWithComputedFields();

        $this->assertFalse($repository->hasField('term', 'brand_slug'));
        $this->assertFalse($repository->hasField('asset', 'brand_slug'));
        $this->assertFalse($repository->hasField('global', 'brand_slug'));
    }

    public function test_computed_field_has_computed_type(): void
    {
        $repository = $this->repositoryWithComputedFields();

        $field = $repository->getField('entry', 'brand_slug');

        $this->assertNotNull($field);
        $this->assertSame('computed', $field->type);
    }

    private function repositoryWithComputedFields(): BlueprintRepository
    {
        $locator = new BlueprintLocator([
            __DIR__.'/../Fixtures/blueprints',
        ]);
        $parser = new BlueprintParser;
        $scanner = new ComputedFieldScanner([
            __DIR__.'/../Fixtures/computed',
        ]);

        return new BlueprintRepository($locator, $parser, $scanner);
    }
}
