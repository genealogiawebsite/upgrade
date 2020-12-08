<?php

namespace LaravelEnso\Upgrade\Services;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use LaravelEnso\Upgrade\Contracts\MigratesData;
use LaravelEnso\Upgrade\Contracts\MigratesPostDataMigration;
use LaravelEnso\Upgrade\Contracts\MigratesTable;
use LaravelEnso\Upgrade\Contracts\RollbackTableMigration;
use LaravelEnso\Upgrade\Contracts\Upgrade;
use ReflectionClass;
use Symfony\Component\Console\Output\ConsoleOutput;

class Database extends Command
{
    protected $output;

    private string $title;
    private string $time;
    private ReflectionClass $reflection;
    private Upgrade $upgrade;

    public function __construct(Upgrade $upgrade)
    {
        parent::__construct();

        $this->upgrade = $upgrade;
        $this->reflection = (new ReflectionClass($upgrade));
        $this->output = new ConsoleOutput();
        $this->title = $this->title();
    }

    private function title(): string
    {
        $reflection = $this->upgrade instanceof Structure
            ? $this->upgrade->reflection()
            : $this->reflection;

        $snake = Str::snake($reflection->getShortName());

        return Str::ucfirst(str_replace('_', ' ', $snake));
    }

    public function handle()
    {
        if ($this->upgrade->isMigrated()) {
            $this->info("{$this->title} has been already done");
        } else {
            $this->start()->migrate()->end();
        }
    }

    private function start()
    {
        $this->time = microtime(true);

        $this->info("{$this->title} is starting");

        return $this;
    }

    private function migrate()
    {
        if ($this->migratesTable()) {
            $this->upgrade->migrateTable();
        }

        try {
            if ($this->migratesData()) {
                DB::transaction(fn () => $this->upgrade->migrateData());
            }

            if ($this->migratesPostDataMigration()) {
                $this->upgrade->migratePostDataMigration();
            }
        } catch (Exception $exception) {
            if ($this->rollbacksTableMigration()) {
                $this->upgrade->rollbackTableMigration();
            }

            $this->error("{$this->title} failed, doing rollback");

            throw $exception;
        }

        return $this;
    }

    private function end()
    {
        $time = (int) ((microtime(true) - $this->time) * 1000);
        $this->info("{$this->title} was done ({$time} ms)");
    }

    private function migratesTable(): bool
    {
        return $this->reflection->implementsInterface(MigratesTable::class);
    }

    private function migratesData(): bool
    {
        return $this->reflection->implementsInterface(MigratesData::class);
    }

    private function migratesPostDataMigration(): bool
    {
        return $this->reflection->implementsInterface(MigratesPostDataMigration::class);
    }

    private function rollbacksTableMigration(): bool
    {
        return $this->reflection->implementsInterface(RollbackTableMigration::class);
    }

    public function line($string, $style = null, $verbosity = null)
    {
        if (! App::runningUnitTests()) {
            parent::line(...func_get_args());
        }
    }
}
