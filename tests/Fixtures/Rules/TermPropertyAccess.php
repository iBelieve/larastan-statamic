<?php

declare(strict_types=1);

namespace IBelieve\LarastanStatamic\Tests\Fixtures\Rules;

use Statamic\Taxonomies\LocalizedTerm;

class TermPropertyAccess
{
    public function validField(LocalizedTerm $term): string
    {
        return $term->description;
    }

    public function undefinedField(LocalizedTerm $term): mixed
    {
        return $term->nonexistent_term_field;
    }
}
