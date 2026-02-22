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

final class GlobalSetFindExtension implements DynamicStaticMethodReturnTypeExtension
{
    /** @var array<string, true> */
    private const SUPPORTED_METHODS = [
        'find' => true,
        'findByHandle' => true,
        'findOrFail' => true,
    ];

    public function getClass(): string
    {
        return 'Statamic\Facades\GlobalSet';
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
            'find', 'findByHandle' => new UnionType([
                new ObjectType('Statamic\Globals\GlobalSet'),
                new NullType,
            ]),
            'findOrFail' => new ObjectType('Statamic\Globals\GlobalSet'),
            default => null,
        };
    }
}
