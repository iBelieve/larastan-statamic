<?php

namespace App\Providers;

class FqcnComputedServiceProvider
{
    public function boot(): void
    {
        \Statamic\Facades\Collection::computed('blog', 'fqcn_field', function ($entry) {
            return 'value';
        });
    }
}
