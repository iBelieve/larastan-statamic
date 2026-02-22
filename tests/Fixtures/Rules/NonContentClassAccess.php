<?php

declare(strict_types=1);

namespace IBelieve\LarastanStatamic\Tests\Fixtures\Rules;

class SomeOtherClass
{
    public string $foo = 'bar';
}

class NonContentClassAccess
{
    public function accessNonContentClass(SomeOtherClass $obj): string
    {
        return $obj->foo;
    }
}
