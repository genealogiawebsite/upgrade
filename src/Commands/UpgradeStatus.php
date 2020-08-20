<?php

namespace LaravelEnso\Upgrade\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use LaravelEnso\Upgrade\Services\UpgradeStatus as Service;

class UpgradeStatus extends Command
{
    protected $signature = 'enso:upgrade:status';

    protected $description = 'This command will print upgrades status';

    public function handle()
    {
        $this->table(
            ['Ran?', 'Package', 'Upgrade', 'Priority', 'Modified At'],
            $this->rows()
        );
    }

    private function rows(): Collection
    {
        return (new Service())
            ->handle()
            ->map(fn ($status) => [
                'Ran?' => $status['isMigrated'] ? $this->green('Yes') : $this->red('No'),
                'Package' => $this->package($status['namespace']),
                'Upgrade' => $this->class($status['namespace']),
                'Priority' => $status['priority'],
                'Modified At' => $status['changedAt']->format(Config::get('enso.config.dateTimeFormat')).
                    " ({$status['changedAt']->diffForHumans()})",
            ]);
    }

    private function green($label): string
    {
        return "<info>$label</info>";
    }

    private function red($label): string
    {
        return "<fg=red>$label</fg=red>";
    }

    private function package($upgrade): string
    {
        return Str::startsWith($upgrade, 'App')
            ? 'App'
            : explode('\\', $upgrade)[1];
    }

    private function class($upgrade): string
    {
        return last(explode('\\', $upgrade));
    }
}