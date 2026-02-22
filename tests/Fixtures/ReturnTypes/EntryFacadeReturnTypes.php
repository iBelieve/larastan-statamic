<?php

declare(strict_types=1);

namespace IBelieve\LarastanStatamic\Tests\Fixtures\ReturnTypes;

use Statamic\Entries\Entry;
use Statamic\Facades\Entry as EntryFacade;
use Statamic\Stache\Query\EntryQueryBuilder;

use function PHPStan\Testing\assertType;

class EntryFacadeReturnTypes
{
    public function testQuery(): void
    {
        assertType('Statamic\Stache\Query\EntryQueryBuilder', EntryFacade::query());
    }

    public function testFind(): void
    {
        assertType('Statamic\Entries\Entry|null', EntryFacade::find('123'));
    }

    public function testFindOrFail(): void
    {
        assertType('Statamic\Entries\Entry', EntryFacade::findOrFail('123'));
    }

    public function testFindByUri(): void
    {
        assertType('Statamic\Entries\Entry|null', EntryFacade::findByUri('/blog/hello'));
    }

    public function testMake(): void
    {
        assertType('Statamic\Entries\Entry', EntryFacade::make());
    }
}
