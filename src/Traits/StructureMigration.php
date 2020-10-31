<?php

namespace LaravelEnso\Upgrade\Traits;

trait StructureMigration
{
    public function permissions(): array
    {
        return $this->permissions;
    }

    public function roles(): array
    {
        return $this->roles ?? [];
    }
}
