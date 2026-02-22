<?php

declare(strict_types=1);

namespace IBelieve\LarastanStatamic\Tests\Fixtures\Rules;

use Statamic\Assets\Asset;

class AssetPropertyAccess
{
    public function validField(Asset $asset): string
    {
        return $asset->alt;
    }

    public function validUniversalField(Asset $asset): string
    {
        return $asset->path;
    }

    public function undefinedField(Asset $asset): mixed
    {
        return $asset->nonexistent_asset_field;
    }
}
