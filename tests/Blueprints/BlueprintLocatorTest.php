<?php

declare(strict_types=1);

namespace IBelieve\LarastanStatamic\Tests\Blueprints;

use IBelieve\LarastanStatamic\Blueprints\BlueprintLocator;
use PHPUnit\Framework\TestCase;

final class BlueprintLocatorTest extends TestCase
{
    private BlueprintLocator $locator;

    protected function setUp(): void
    {
        $this->locator = new BlueprintLocator([
            __DIR__ . '/../Fixtures/blueprints',
        ]);
    }

    public function test_locates_all_blueprints(): void
    {
        $locations = $this->locator->locateAll();

        $this->assertNotEmpty($locations);

        $contentTypes = array_unique(array_column($locations, 'contentType'));
        sort($contentTypes);

        $this->assertSame(['asset', 'entry', 'global', 'term'], $contentTypes);
    }

    public function test_locates_entry_blueprints(): void
    {
        $locations = $this->locator->locateForContentType('entry');

        $this->assertCount(1, $locations);
        $this->assertSame('entry', $locations[0]['contentType']);
        $this->assertSame('blog', $locations[0]['group']);
        $this->assertStringEndsWith('article.yaml', $locations[0]['path']);
    }

    public function test_locates_term_blueprints(): void
    {
        $locations = $this->locator->locateForContentType('term');

        $this->assertCount(1, $locations);
        $this->assertSame('term', $locations[0]['contentType']);
        $this->assertSame('tags', $locations[0]['group']);
    }

    public function test_locates_global_blueprints(): void
    {
        $locations = $this->locator->locateForContentType('global');

        $this->assertCount(1, $locations);
        $this->assertSame('global', $locations[0]['contentType']);
        $this->assertSame('settings', $locations[0]['group']);
    }

    public function test_locates_asset_blueprints(): void
    {
        $locations = $this->locator->locateForContentType('asset');

        $this->assertCount(1, $locations);
        $this->assertSame('asset', $locations[0]['contentType']);
        $this->assertSame('images', $locations[0]['group']);
    }

    public function test_handles_nonexistent_paths(): void
    {
        $locator = new BlueprintLocator(['/nonexistent/path']);

        $locations = $locator->locateAll();

        $this->assertSame([], $locations);
    }

    public function test_handles_empty_paths(): void
    {
        $locator = new BlueprintLocator([]);

        $locations = $locator->locateAll();

        $this->assertSame([], $locations);
    }

    public function test_handles_multiple_blueprint_paths(): void
    {
        $locator = new BlueprintLocator([
            __DIR__ . '/../Fixtures/blueprints',
            '/nonexistent/path',
        ]);

        $locations = $locator->locateAll();

        $this->assertNotEmpty($locations);
    }
}
