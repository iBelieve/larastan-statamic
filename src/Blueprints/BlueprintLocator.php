<?php

declare(strict_types=1);

namespace IBelieve\LarastanStatamic\Blueprints;

/**
 * @phpstan-type ContentType 'entry'|'term'|'asset'|'global'
 * @phpstan-type BlueprintLocation array{contentType: ContentType, path: string, group: string}
 */
final class BlueprintLocator
{
    /**
     * @param list<string> $blueprintPaths
     */
    public function __construct(
        private readonly array $blueprintPaths,
    ) {}

    /**
     * @return list<BlueprintLocation>
     */
    public function locateAll(): array
    {
        $locations = [];

        foreach ($this->blueprintPaths as $basePath) {
            if (! is_dir($basePath)) {
                continue;
            }

            $locations = array_merge($locations, $this->locateInPath($basePath));
        }

        return $locations;
    }

    /**
     * @param ContentType $contentType
     * @return list<BlueprintLocation>
     */
    public function locateForContentType(string $contentType): array
    {
        return array_values(array_filter(
            $this->locateAll(),
            static fn (array $loc): bool => $loc['contentType'] === $contentType,
        ));
    }

    /**
     * @return list<BlueprintLocation>
     */
    private function locateInPath(string $basePath): array
    {
        $locations = [];

        $locations = array_merge($locations, $this->scanDirectory($basePath, 'collections', 'entry'));
        $locations = array_merge($locations, $this->scanDirectory($basePath, 'taxonomies', 'term'));
        $locations = array_merge($locations, $this->scanFlatDirectory($basePath, 'globals', 'global'));
        $locations = array_merge($locations, $this->scanFlatDirectory($basePath, 'assets', 'asset'));

        return $locations;
    }

    /**
     * Scan nested directories: {basePath}/{dirName}/{group}/{blueprint}.yaml
     *
     * @param ContentType $contentType
     * @return list<BlueprintLocation>
     */
    private function scanDirectory(string $basePath, string $dirName, string $contentType): array
    {
        $dir = $basePath . '/' . $dirName;

        if (! is_dir($dir)) {
            return [];
        }

        $locations = [];
        $groups = scandir($dir);

        if ($groups === false) {
            return [];
        }

        foreach ($groups as $group) {
            if ($group === '.' || $group === '..') {
                continue;
            }

            $groupDir = $dir . '/' . $group;

            if (! is_dir($groupDir)) {
                continue;
            }

            $files = glob($groupDir . '/*.yaml');

            if ($files === false) {
                continue;
            }

            foreach ($files as $file) {
                $locations[] = [
                    'contentType' => $contentType,
                    'path' => $file,
                    'group' => $group,
                ];
            }
        }

        return $locations;
    }

    /**
     * Scan flat directory: {basePath}/{dirName}/{blueprint}.yaml
     *
     * @param ContentType $contentType
     * @return list<BlueprintLocation>
     */
    private function scanFlatDirectory(string $basePath, string $dirName, string $contentType): array
    {
        $dir = $basePath . '/' . $dirName;

        if (! is_dir($dir)) {
            return [];
        }

        $files = glob($dir . '/*.yaml');

        if ($files === false) {
            return [];
        }

        $locations = [];

        foreach ($files as $file) {
            $group = pathinfo($file, PATHINFO_FILENAME);
            $locations[] = [
                'contentType' => $contentType,
                'path' => $file,
                'group' => $group,
            ];
        }

        return $locations;
    }
}
