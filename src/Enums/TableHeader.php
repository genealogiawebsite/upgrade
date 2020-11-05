<?php

namespace LaravelEnso\Upgrade\Enums;

use LaravelEnso\Enums\Services\Enum;

class TableHeader extends Enum
{
    public const NrCrt = 1;
    public const Package = 2;
    public const Upgrade = 3;
    public const Applicable = 4;
    public const Manual = 5;
    public const Priority = 6;
    public const Migration = 7;
    public const Ran = 8;
    public const ChangedAt = 9;

    protected static array $data = [
        self::NrCrt => 'Nr Crt',
        self::Package => 'Package',
        self::Upgrade => 'Upgrade',
        self::Applicable => 'Applicable',
        self::Manual => 'Manual',
        self::Priority => 'Priority',
        self::Migration => 'Migration',
        self::Ran => 'Ran',
        self::ChangedAt => 'Changed At',
    ];
}
