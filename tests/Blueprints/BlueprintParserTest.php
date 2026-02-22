<?php

declare(strict_types=1);

namespace IBelieve\LarastanStatamic\Tests\Blueprints;

use IBelieve\LarastanStatamic\Blueprints\BlueprintParser;
use PHPUnit\Framework\TestCase;

final class BlueprintParserTest extends TestCase
{
    private BlueprintParser $parser;

    protected function setUp(): void
    {
        $this->parser = new BlueprintParser;
    }

    public function test_parses_blueprint_with_tabs_and_sections(): void
    {
        $fields = $this->parser->parse($this->fixturePath('collections/blog/article.yaml'));

        $this->assertNotEmpty($fields);

        $handles = array_map(fn ($f) => $f->handle, $fields);

        $this->assertContains('body', $handles);
        $this->assertContains('featured', $handles);
        $this->assertContains('event_date', $handles);
        $this->assertContains('hero_image', $handles);
        $this->assertContains('related_posts', $handles);
    }

    public function test_extracts_field_types(): void
    {
        $fields = $this->parser->parse($this->fixturePath('collections/blog/article.yaml'));
        $fieldMap = [];

        foreach ($fields as $field) {
            $fieldMap[$field->handle] = $field;
        }

        $this->assertSame('markdown', $fieldMap['body']->type);
        $this->assertSame('toggle', $fieldMap['featured']->type);
        $this->assertSame('date', $fieldMap['event_date']->type);
        $this->assertSame('assets', $fieldMap['hero_image']->type);
        $this->assertSame('entries', $fieldMap['related_posts']->type);
        $this->assertSame('integer', $fieldMap['views']->type);
        $this->assertSame('float', $fieldMap['rating']->type);
        $this->assertSame('select', $fieldMap['category']->type);
        $this->assertSame('bard', $fieldMap['content']->type);
        $this->assertSame('grid', $fieldMap['metadata']->type);
        $this->assertSame('text', $fieldMap['template_name']->type);
    }

    public function test_extracts_config_options(): void
    {
        $fields = $this->parser->parse($this->fixturePath('collections/blog/article.yaml'));
        $fieldMap = [];

        foreach ($fields as $field) {
            $fieldMap[$field->handle] = $field;
        }

        // hero_image has max_items: 1
        $this->assertTrue($fieldMap['hero_image']->isSingleRelationship());
        $this->assertSame(1, $fieldMap['hero_image']->getMaxItems());

        // gallery does not have max_items
        $this->assertFalse($fieldMap['gallery']->isSingleRelationship());
        $this->assertNull($fieldMap['gallery']->getMaxItems());

        // author has max_items: 1
        $this->assertTrue($fieldMap['author']->isSingleRelationship());
    }

    public function test_parses_taxonomy_blueprint(): void
    {
        $fields = $this->parser->parse($this->fixturePath('taxonomies/tags/tag.yaml'));

        $this->assertCount(2, $fields);

        $handles = array_map(fn ($f) => $f->handle, $fields);

        $this->assertContains('description', $handles);
        $this->assertContains('icon', $handles);
    }

    public function test_parses_global_blueprint(): void
    {
        $fields = $this->parser->parse($this->fixturePath('globals/settings.yaml'));

        $handles = array_map(fn ($f) => $f->handle, $fields);

        $this->assertContains('site_name', $handles);
        $this->assertContains('maintenance_mode', $handles);
        $this->assertContains('analytics_id', $handles);
    }

    public function test_parses_asset_blueprint(): void
    {
        $fields = $this->parser->parse($this->fixturePath('assets/images.yaml'));

        $handles = array_map(fn ($f) => $f->handle, $fields);

        $this->assertContains('alt', $handles);
        $this->assertContains('caption', $handles);
        $this->assertContains('photographer', $handles);
    }

    public function test_returns_empty_for_nonexistent_file(): void
    {
        $fields = $this->parser->parse('/nonexistent/path.yaml');

        $this->assertSame([], $fields);
    }

    public function test_returns_empty_for_empty_file(): void
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'blueprint_');
        assert($tempFile !== false);
        file_put_contents($tempFile, '');

        $fields = $this->parser->parse($tempFile);

        $this->assertSame([], $fields);

        unlink($tempFile);
    }

    private function fixturePath(string $relativePath): string
    {
        return __DIR__.'/../Fixtures/blueprints/'.$relativePath;
    }
}
