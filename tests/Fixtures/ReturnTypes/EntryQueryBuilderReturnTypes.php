<?php

declare(strict_types=1);

namespace IBelieve\LarastanStatamic\Tests\Fixtures\ReturnTypes;

use Statamic\Stache\Query\EntryQueryBuilder;

use function PHPStan\Testing\assertType;

class EntryQueryBuilderReturnTypes
{
    public function testGet(EntryQueryBuilder $builder): void
    {
        assertType('Illuminate\Support\Collection<int, Statamic\Entries\Entry>', $builder->get());
    }

    public function testFirst(EntryQueryBuilder $builder): void
    {
        assertType('Statamic\Entries\Entry|null', $builder->first());
    }

    public function testFind(EntryQueryBuilder $builder): void
    {
        assertType('Statamic\Entries\Entry|null', $builder->find('123'));
    }

    public function testFirstOrFail(EntryQueryBuilder $builder): void
    {
        assertType('Statamic\Entries\Entry', $builder->firstOrFail());
    }

    public function testCount(EntryQueryBuilder $builder): void
    {
        assertType('int', $builder->count());
    }

    public function testExists(EntryQueryBuilder $builder): void
    {
        assertType('bool', $builder->exists());
    }
}
