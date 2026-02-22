<?php

declare(strict_types=1);

namespace IBelieve\LarastanStatamic\Tests\Fixtures\Rules;

use Statamic\Entries\Entry;

class EntryPropertyAccess
{
    public function validField(Entry $entry): string
    {
        return $entry->body;
    }

    public function validUniversalField(Entry $entry): string
    {
        return $entry->title;
    }

    public function undefinedField(Entry $entry): mixed
    {
        return $entry->nonexistent_field;
    }

    public function anotherUndefinedField(Entry $entry): mixed
    {
        return $entry->does_not_exist;
    }

    public function multipleAccesses(Entry $entry): void
    {
        $a = $entry->body;
        $b = $entry->nonexistent_field;
        $c = $entry->title;
    }
}
