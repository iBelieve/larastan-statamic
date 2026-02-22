<?php

declare(strict_types=1);

namespace IBelieve\LarastanStatamic\Blueprints;

use Symfony\Component\Yaml\Yaml;

final class BlueprintParser
{
    /**
     * @return list<FieldDefinition>
     */
    public function parse(string $filePath): array
    {
        if (! file_exists($filePath)) {
            return [];
        }

        $content = file_get_contents($filePath);

        if ($content === false || $content === '') {
            return [];
        }

        /** @var mixed $data */
        $data = Yaml::parse($content);

        if (! is_array($data)) {
            return [];
        }

        return $this->extractFields($data);
    }

    /**
     * @param array<mixed> $data
     * @return list<FieldDefinition>
     */
    private function extractFields(array $data): array
    {
        $fields = [];

        // Statamic blueprint format: tabs > tab_name > sections > [] > fields
        if (isset($data['tabs']) && is_array($data['tabs'])) {
            foreach ($data['tabs'] as $tab) {
                if (! is_array($tab)) {
                    continue;
                }

                $fields = array_merge($fields, $this->extractFieldsFromTab($tab));
            }
        }

        // Legacy/alternative format: sections > section_name > fields
        if (isset($data['sections']) && is_array($data['sections'])) {
            foreach ($data['sections'] as $section) {
                if (! is_array($section)) {
                    continue;
                }

                $fields = array_merge($fields, $this->extractFieldsFromSection($section));
            }
        }

        // Top-level fields (no sections/tabs)
        if (isset($data['fields']) && is_array($data['fields'])) {
            $fields = array_merge($fields, $this->parseFieldList($data['fields']));
        }

        return $fields;
    }

    /**
     * Extract fields from a tab, which contains sections.
     *
     * @param array<mixed> $tab
     * @return list<FieldDefinition>
     */
    private function extractFieldsFromTab(array $tab): array
    {
        $fields = [];

        // A tab can have a 'sections' array
        if (isset($tab['sections']) && is_array($tab['sections'])) {
            foreach ($tab['sections'] as $section) {
                if (! is_array($section)) {
                    continue;
                }

                $fields = array_merge($fields, $this->extractFieldsFromSection($section));
            }
        }

        // A tab can also have fields directly
        if (isset($tab['fields']) && is_array($tab['fields'])) {
            $fields = array_merge($fields, $this->parseFieldList($tab['fields']));
        }

        return $fields;
    }

    /**
     * @param array<mixed> $section
     * @return list<FieldDefinition>
     */
    private function extractFieldsFromSection(array $section): array
    {
        if (! isset($section['fields']) || ! is_array($section['fields'])) {
            return [];
        }

        return $this->parseFieldList($section['fields']);
    }

    /**
     * @param array<mixed> $fieldList
     * @return list<FieldDefinition>
     */
    private function parseFieldList(array $fieldList): array
    {
        $fields = [];

        foreach ($fieldList as $fieldEntry) {
            if (! is_array($fieldEntry)) {
                continue;
            }

            // Skip imported fieldsets for now
            if (isset($fieldEntry['import'])) {
                continue;
            }

            if (! isset($fieldEntry['handle']) || ! is_string($fieldEntry['handle'])) {
                continue;
            }

            $handle = $fieldEntry['handle'];
            $fieldData = $fieldEntry['field'] ?? $fieldEntry;

            if (! is_array($fieldData)) {
                continue;
            }

            $type = $fieldData['type'] ?? 'text';

            if (! is_string($type)) {
                continue;
            }

            $config = array_filter($fieldData, static function (mixed $value, string|int $key): bool {
                return ! in_array($key, ['type', 'display', 'instructions', 'handle'], true);
            }, ARRAY_FILTER_USE_BOTH);

            /** @var array<string, mixed> $config */

            $fields[] = new FieldDefinition(
                handle: $handle,
                type: $type,
                config: $config,
            );
        }

        return $fields;
    }
}
