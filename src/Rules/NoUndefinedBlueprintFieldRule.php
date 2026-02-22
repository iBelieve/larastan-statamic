<?php

declare(strict_types=1);

namespace IBelieve\LarastanStatamic\Rules;

use IBelieve\LarastanStatamic\Blueprints\BlueprintRepository;
use IBelieve\LarastanStatamic\Support\ContentClassMap;
use PhpParser\Node;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Identifier;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * @implements Rule<PropertyFetch>
 */
final class NoUndefinedBlueprintFieldRule implements Rule
{
    public function __construct(
        private readonly BlueprintRepository $repository,
    ) {}

    public function getNodeType(): string
    {
        return PropertyFetch::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if (! $node->name instanceof Identifier) {
            return [];
        }

        $propertyName = $node->name->name;
        $varType = $scope->getType($node->var);

        foreach ($varType->getObjectClassReflections() as $classReflection) {
            $contentType = ContentClassMap::getContentType($classReflection);

            if ($contentType === null) {
                continue;
            }

            // Skip universal properties
            $universalProps = ContentClassMap::getUniversalProperties($contentType);

            if (isset($universalProps[$propertyName])) {
                return [];
            }

            // Check if the field exists in any blueprint
            if ($this->repository->hasField($contentType, $propertyName)) {
                return [];
            }

            return [
                RuleErrorBuilder::message(
                    sprintf(
                        'Access to field \'%s\' on %s that is not defined in any blueprint.',
                        $propertyName,
                        $classReflection->getDisplayName(),
                    ),
                )
                    ->identifier('statamic.undefinedField')
                    ->build(),
            ];
        }

        return [];
    }
}
