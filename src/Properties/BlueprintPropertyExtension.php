<?php

declare(strict_types=1);

namespace IBelieve\LarastanStatamic\Properties;

use IBelieve\LarastanStatamic\Blueprints\BlueprintRepository;
use IBelieve\LarastanStatamic\Support\ContentClassMap;
use IBelieve\LarastanStatamic\Types\FieldtypeMapper;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\PropertiesClassReflectionExtension;
use PHPStan\Reflection\PropertyReflection;

final class BlueprintPropertyExtension implements PropertiesClassReflectionExtension
{
    public function __construct(
        private readonly BlueprintRepository $repository,
        private readonly FieldtypeMapper $fieldtypeMapper,
    ) {}

    public function hasProperty(ClassReflection $classReflection, string $propertyName): bool
    {
        $contentType = ContentClassMap::getContentType($classReflection);

        if ($contentType === null) {
            return false;
        }

        // Check universal properties first
        $universalProps = ContentClassMap::getUniversalProperties($contentType);

        if (isset($universalProps[$propertyName])) {
            return true;
        }

        // Check blueprint fields
        return $this->repository->hasField($contentType, $propertyName);
    }

    public function getProperty(ClassReflection $classReflection, string $propertyName): PropertyReflection
    {
        $contentType = ContentClassMap::getContentType($classReflection);
        assert($contentType !== null);

        // Check universal properties first
        $universalProps = ContentClassMap::getUniversalProperties($contentType);

        if (isset($universalProps[$propertyName])) {
            return new BlueprintPropertyReflection($classReflection, $universalProps[$propertyName]);
        }

        // Get from blueprint
        $field = $this->repository->getField($contentType, $propertyName);
        assert($field !== null);

        $type = $this->fieldtypeMapper->mapToType($field);

        return new BlueprintPropertyReflection($classReflection, $type);
    }
}
