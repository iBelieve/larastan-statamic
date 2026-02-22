<?php

declare(strict_types=1);

namespace IBelieve\LarastanStatamic\Tests\Fixtures\ReturnTypes;

use Statamic\Facades\GlobalSet;

use function PHPStan\Testing\assertType;

class GlobalSetFindReturnTypes
{
    public function testFind(): void
    {
        assertType('Statamic\Globals\GlobalSet|null', GlobalSet::find('settings'));
    }

    public function testFindByHandle(): void
    {
        assertType('Statamic\Globals\GlobalSet|null', GlobalSet::findByHandle('settings'));
    }

    public function testFindOrFail(): void
    {
        assertType('Statamic\Globals\GlobalSet', GlobalSet::findOrFail('settings'));
    }
}
