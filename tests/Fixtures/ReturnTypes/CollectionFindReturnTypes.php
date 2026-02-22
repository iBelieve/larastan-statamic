<?php

declare(strict_types=1);

namespace IBelieve\LarastanStatamic\Tests\Fixtures\ReturnTypes;

use Statamic\Facades\Collection;

use function PHPStan\Testing\assertType;

class CollectionFindReturnTypes
{
    public function testFind(): void
    {
        assertType('Statamic\Entries\Collection|null', Collection::find('blog'));
    }

    public function testFindByHandle(): void
    {
        assertType('Statamic\Entries\Collection|null', Collection::findByHandle('blog'));
    }

    public function testFindOrFail(): void
    {
        assertType('Statamic\Entries\Collection', Collection::findOrFail('blog'));
    }
}
