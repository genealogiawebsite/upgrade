<?php

namespace LaravelEnso\Upgrade\App\Services;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use LaravelEnso\Upgrade\App\Contracts\MigratesData;
use LaravelEnso\Upgrade\App\Contracts\MigratesPostDataMigration;
use LaravelEnso\Upgrade\App\Contracts\MigratesTable;
use LaravelEnso\Upgrade\App\Contracts\RollbackTableMigration;
use LaravelEnso\Upgrade\App\Contracts\Upgrade;
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
        $this->title = $this->title();
        $this->output = new ConsoleOutput();
    }

    private function title(): string
    {
        return Str::snake($this->reflection->getShortName());
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

            $this->error("{$this->title} was unsuccessfully, doing rollback");

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
