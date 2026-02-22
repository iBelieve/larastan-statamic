<?php

declare(strict_types=1);

namespace IBelieve\LarastanStatamic\ReturnTypes;

use PhpParser\Node\Expr\StaticCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\DynamicStaticMethodReturnTypeExtension;
use PHPStan\Type\NullType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;

final class EntryFacadeExtension implements DynamicStaticMethodReturnTypeExtension
{
    /** @var array<string, true> */
    private const SUPPORTED_METHODS = [
        'query' => true,
        'find' => true,
        'findOrFail' => true,
        'findByUri' => true,
        'make' => true,
    ];

    public function getClass(): string
    {
        return 'Statamic\Facades\Entry';
    }

    public function isStaticMethodSupported(MethodReflection $methodReflection): bool
    {
        return isset(self::SUPPORTED_METHODS[$methodReflection->getName()]);
    }

    public function getTypeFromStaticMethodCall(
        MethodReflection $methodReflection,
        StaticCall $methodCall,
        Scope $scope,
    ): ?Type {
        return match ($methodReflection->getName()) {
            'query' => new ObjectType('Statamic\Stache\Query\EntryQueryBuilder'),
            'find' => new UnionType([new ObjectType('Statamic\Entries\Entry'), new NullType]),
            'findOrFail' => new ObjectType('Statamic\Entries\Entry'),
            'findByUri' => new UnionType([new ObjectType('Statamic\Entries\Entry'), new NullType]),
            'make' => new ObjectType('Statamic\Entries\Entry'),
            default => null,
        };
    }
}
