<?php

namespace LaravelEnso\Upgrade\Services;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use LaravelEnso\Upgrade\Contracts\Applicable;
use LaravelEnso\Upgrade\Contracts\BeforeMigration;
use LaravelEnso\Upgrade\Contracts\ShouldRunManually;
use LaravelEnso\Upgrade\Contracts\Upgrade as Contract;
use LaravelEnso\Upgrade\Enums\TableHeader;

class UpgradeStatus extends Upgrade
{
    public function handle()
    {
        return $this->sorted()->values()->map(fn (Contract $upgrade, $index) => [
            TableHeader::NrCrt => $index + 1,
            TableHeader::Package => $this->package($this->reflection($upgrade)->getName()),
            TableHeader::Upgrade => $this->upgrade($this->reflection($upgrade)->getName()),
            TableHeader::Applicable => $this->applicable($upgrade),
            TableHeader::Manual => $this->isManual($upgrade),
            TableHeader::Priority => $this->priority($upgrade),
            TableHeader::Migration => $this->migration($upgrade),
            TableHeader::Ran => $this->ran($upgrade),
            TableHeader::ChangedAt => $this->changedAt($upgrade),
        ]);
    }

    private function applicable(Contract $upgrade): string
    {
        return ! $upgrade instanceof Applicable || $upgrade->applicable()
            ? $this->green('Yes')
            : $this->yellow('No');
    }

    private function isManual(Contract $upgrade): string
    {
        return $upgrade instanceof ShouldRunManually
            ? $this->yellow('Yes')
            : $this->green('No');
    }

    private function changedAt(Contract $upgrade): string
    {
        $lastModifiedAt = $this->lastModifiedAt($upgrade);

        return $lastModifiedAt->format(Config::get('enso.config.dateTimeFormat')).
            " ({$lastModifiedAt->diffForHumans()})";
    }

    private function migration(Contract $upgrade): string
    {
        return $upgrade instanceof BeforeMigration
            ? $this->yellow('Before')
            : $this->green('After');
    }

    private function ran(Contract $upgrade): string
    {
        return $upgrade->isMigrated()
            ? $this->green('Yes')
            : $this->red('No');
    }

    private function green($label): string
    {
        return "<info>{$label}</info>";
    }

    private function red($label): string
    {
        return "<fg=red>{$label}</fg=red>";
    }

    private function yellow($label): string
    {
        return "<fg=yellow>{$label}</fg=yellow>";
    }

    private function package($namespace): string
    {
        return Str::startsWith($namespace, 'App')
            ? 'app'
            : Str::lower(explode('\\', $namespace)[1]);
    }

    private function upgrade($namespace): string
    {
        return last(explode('\\', $namespace));
    }
}
