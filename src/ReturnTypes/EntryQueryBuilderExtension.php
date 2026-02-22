<?php

declare(strict_types=1);

namespace IBelieve\LarastanStatamic\ReturnTypes;

use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\BooleanType;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\Generic\GenericObjectType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\NullType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;

final class EntryQueryBuilderExtension implements DynamicMethodReturnTypeExtension
{
    /** @var array<string, true> */
    private const SUPPORTED_METHODS = [
        'get' => true,
        'first' => true,
        'find' => true,
        'firstOrFail' => true,
        'count' => true,
        'exists' => true,
    ];

    /** @var array<string, string> */
    private const BUILDER_TO_MODEL = [
        'Statamic\Stache\Query\EntryQueryBuilder' => 'Statamic\Entries\Entry',
        'Statamic\Stache\Query\TermQueryBuilder' => 'Statamic\Taxonomies\LocalizedTerm',
        'Statamic\Assets\QueryBuilder' => 'Statamic\Assets\Asset',
    ];

    public function getClass(): string
    {
        return 'Statamic\Query\Builder';
    }

    public function isMethodSupported(MethodReflection $methodReflection): bool
    {
        return isset(self::SUPPORTED_METHODS[$methodReflection->getName()]);
    }

    public function getTypeFromMethodCall(
        MethodReflection $methodReflection,
        MethodCall $methodCall,
        Scope $scope,
    ): ?Type {
        $callerType = $scope->getType($methodCall->var);
        $methodName = $methodReflection->getName();

        $modelClass = $this->resolveModelClass($callerType);

        if ($modelClass === null) {
            return null;
        }

        return match ($methodName) {
            'get' => new GenericObjectType('Illuminate\Support\Collection', [
                new IntegerType,
                new ObjectType($modelClass),
            ]),
            'first' => new UnionType([new ObjectType($modelClass), new NullType]),
            'find' => new UnionType([new ObjectType($modelClass), new NullType]),
            'firstOrFail' => new ObjectType($modelClass),
            'count' => new IntegerType,
            'exists' => new BooleanType,
            default => null,
        };
    }

    private function resolveModelClass(Type $callerType): ?string
    {
        foreach ($callerType->getObjectClassNames() as $className) {
            if (isset(self::BUILDER_TO_MODEL[$className])) {
                return self::BUILDER_TO_MODEL[$className];
            }
        }

        return null;
    }
}
