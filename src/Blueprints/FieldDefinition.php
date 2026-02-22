<?php

declare(strict_types=1);

namespace IBelieve\LarastanStatamic\Blueprints;

/**
 * @phpstan-type FieldConfig array<string, mixed>
 */
final class FieldDefinition
{
    /**
     * @param FieldConfig $config
     */
    public function __construct(
        public readonly string $handle,
        public readonly string $type,
        public readonly array $config = [],
    ) {}

    public function getMaxItems(): ?int
    {
        if (! isset($this->config['max_items'])) {
            return null;
        }

        $maxItems = $this->config['max_items'];

        if (! is_int($maxItems) && ! is_string($maxItems)) {
            return null;
        }

        return (int) $maxItems;
    }

    public function isSingleRelationship(): bool
    {
        return $this->getMaxItems() === 1;
    }
}
