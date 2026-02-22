<?php

namespace App\Providers;

use Statamic\Facades\Collection;

class ComputedServiceProvider
{
    public function boot(): void
    {
        // Single field, single collection
        Collection::computed('articles', 'brand_slug', function ($entry, $value) {
            return $entry->brand?->slug;
        });

        // Multiple collections, single field
        Collection::computed(['articles', 'pages'], 'view_count', function ($entry) {
            return 0;
        });

        // Single collection, multiple fields via associative array
        Collection::computed('articles', [
            'shares' => function ($entry, $value) {
                return 0;
            },
            'likes' => function ($entry, $value) {
                return 0;
            },
        ]);
    }
}
