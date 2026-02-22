<?php

declare(strict_types=1);

namespace IBelieve\LarastanStatamic\Tests\Fixtures\Rules;

use Statamic\Globals\Variables;

class GlobalVariablesAccess
{
    public function validField(Variables $variables): string
    {
        return $variables->site_name;
    }

    public function undefinedField(Variables $variables): mixed
    {
        return $variables->nonexistent_global;
    }
}
