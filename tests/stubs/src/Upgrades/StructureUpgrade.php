<?php

namespace LaravelEnso\TestUpgrade\Upgrades;

use Illuminate\Support\Collection;
use LaravelEnso\Upgrade\Contracts\MigratesStructure;

class StructureUpgrade implements MigratesStructure
{
    public function permissions(): array
    {
        return [];
    }

    public function roles(): array
    {
        return [];
    }
}
