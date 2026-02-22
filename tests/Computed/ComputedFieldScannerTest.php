<?php

declare(strict_types=1);

namespace IBelieve\LarastanStatamic\Tests\Computed;

use IBelieve\LarastanStatamic\Computed\ComputedFieldScanner;
use PHPUnit\Framework\TestCase;

final class ComputedFieldScannerTest extends TestCase
{
    private ComputedFieldScanner $scanner;

    protected function setUp(): void
    {
        $this->scanner = new ComputedFieldScanner([
            __DIR__.'/../Fixtures/computed',
        ]);
    }

    public function test_scans_single_computed_field(): void
    {
        $fields = $this->scanner->scan();
        $handles = array_map(fn ($f) => $f->handle, $fields);

        $this->assertContains('brand_slug', $handles);
    }

    public function test_scans_field_with_multiple_collections(): void
    {
        $fields = $this->scanner->scan();
        $handles = array_map(fn ($f) => $f->handle, $fields);

        $this->assertContains('view_count', $handles);
    }

    public function test_scans_multiple_fields_via_array(): void
    {
        $fields = $this->scanner->scan();
        $handles = array_map(fn ($f) => $f->handle, $fields);

        $this->assertContains('shares', $handles);
        $this->assertContains('likes', $handles);
    }

    public function test_scans_fqcn_facade_call(): void
    {
        $fields = $this->scanner->scan();
        $handles = array_map(fn ($f) => $f->handle, $fields);

        $this->assertContains('fqcn_field', $handles);
    }

    public function test_computed_fields_have_computed_type(): void
    {
        $fields = $this->scanner->scan();

        $this->assertNotEmpty($fields);

        foreach ($fields as $field) {
            $this->assertSame('computed', $field->type);
        }
    }

    public function test_handles_nonexistent_paths(): void
    {
        $scanner = new ComputedFieldScanner(['/nonexistent/path']);

        $this->assertSame([], $scanner->scan());
    }

    public function test_handles_empty_paths(): void
    {
        $scanner = new ComputedFieldScanner([]);

        $this->assertSame([], $scanner->scan());
    }

    public function test_caches_results(): void
    {
        $fields1 = $this->scanner->scan();
        $fields2 = $this->scanner->scan();

        $this->assertSame(count($fields1), count($fields2));
    }
}
