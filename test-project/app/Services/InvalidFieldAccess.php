<?php

declare(strict_types=1);

namespace App\Services;

use Statamic\Entries\Entry;
use Statamic\Globals\Variables;
use Statamic\Taxonomies\LocalizedTerm;

class InvalidFieldAccess
{
    /**
     * These entry field accesses should all produce errors in strict mode,
     * because none of these fields are defined in any entry blueprint.
     */
    public function undefinedEntryFields(Entry $entry): array
    {
        return [
            $entry->subtitle,
            $entry->summary,
            $entry->nonexistent_field,
        ];
    }

    /**
     * These global field accesses should produce errors in strict mode,
     * because none of these fields are defined in the settings blueprint.
     */
    public function undefinedGlobalFields(Variables $variables): array
    {
        return [
            $variables->theme,
            $variables->copyright_text,
        ];
    }

    /**
     * These term field accesses should produce errors in strict mode,
     * because this field is not defined in any taxonomy blueprint.
     */
    public function undefinedTermFields(LocalizedTerm $term): array
    {
        return [
            $term->seo_title,
        ];
    }
}
