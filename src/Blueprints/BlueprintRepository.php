<?php

declare(strict_types=1);

namespace IBelieve\LarastanStatamic\Blueprints;

use IBelieve\LarastanStatamic\Computed\ComputedFieldScanner;

/**
 * @phpstan-import-type ContentType from BlueprintLocator
 */
final class BlueprintRepository
{
    /** @var array<string, list<FieldDefinition>>|null */
    private ?array $cachedFields = null;

    public function __construct(
        private readonly BlueprintLocator $locator,
        private readonly BlueprintParser $parser,
        private readonly ComputedFieldScanner $computedFieldScanner,
    ) {}

    /**
     * @param  ContentType  $contentType
     * @return list<FieldDefinition>
     */
    public function getFieldsForContentType(string $contentType): array
    {
        $allFields = $this->getAllFields();

        return $allFields[$contentType] ?? [];
    }

    /**
     * @param  ContentType  $contentType
     * @return array<string, FieldDefinition>
     */
    public function getFieldMapForContentType(string $contentType): array
    {
        $fields = $this->getFieldsForContentType($contentType);
        $map = [];

        foreach ($fields as $field) {
            // If multiple blueprints define the same field handle, the last one wins.
            // This is acceptable since we're taking the union of all blueprints.
            $map[$field->handle] = $field;
        }

        return $map;
    }

    /**
     * @param  ContentType  $contentType
     */
    public function hasField(string $contentType, string $handle): bool
    {
        $fieldMap = $this->getFieldMapForContentType($contentType);

        return isset($fieldMap[$handle]);
    }

    /**
     * @param  ContentType  $contentType
     */
    public function getField(string $contentType, string $handle): ?FieldDefinition
    {
        $fieldMap = $this->getFieldMapForContentType($contentType);

        return $fieldMap[$handle] ?? null;
    }

    /**
     * @return array<string, list<FieldDefinition>>
     */
    private function getAllFields(): array
    {
        if ($this->cachedFields !== null) {
            return $this->cachedFields;
        }

        $this->cachedFields = [
            'entry' => [],
            'term' => [],
            'asset' => [],
            'global' => [],
        ];

        $locations = $this->locator->locateAll();

        foreach ($locations as $location) {
            $fields = $this->parser->parse($location['path']);
            $this->cachedFields[$location['contentType']] = array_merge(
                $this->cachedFields[$location['contentType']],
                $fields,
            );
        }

        // Computed fields only apply to entries
        $this->cachedFields['entry'] = array_merge(
            $this->cachedFields['entry'],
            $this->computedFieldScanner->scan(),
        );

        return $this->cachedFields;
    }
}
