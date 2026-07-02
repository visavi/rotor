<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Чанковая конверсия с транзакцией на чанк: createFromTimestamp/parse сохраняют
     * историческую таймзону (старый DST), а батч-коммит убирает fsync-на-строку.
     * whereNull по $resumeCol позволяет продолжить с места падения (таймаут на шареде).
     */
    private function convert(string $table, string $resumeCol, callable $map): void
    {
        DB::table($table)->select(['id', 'created_at'])
            ->whereNull($resumeCol)
            ->orderBy('id')
            ->chunkById(5000, function ($rows) use ($table, $map) {
                DB::transaction(function () use ($rows, $table, $map) {
                    foreach ($rows as $row) {
                        DB::table($table)->where('id', $row->id)->update($map($row));
                    }
                });
            });
    }

    /**
     * Создаёт только отсутствующие временные колонки: упавший upgrade мог
     * оставить их с прошлого запуска, повторный запуск не должен падать.
     */
    private function addTempColumns(string $table, string $type, array $cols): void
    {
        $missing = array_filter($cols, static fn ($col) => ! Schema::hasColumn($table, $col));

        if ($missing) {
            Schema::table($table, static function (Blueprint $blueprint) use ($type, $missing) {
                foreach ($missing as $col) {
                    $blueprint->{$type}($col)->nullable();
                }
            });
        }
    }

    public function up(): void
    {
        $toDt = static fn ($v) => $v ? Date::createFromTimestamp($v, config('app.timezone'))->format('Y-m-d H:i:s') : null;

        // ban — без индекса на created_at
        if (Schema::getColumnType('ban', 'created_at') !== 'datetime') {
            $this->addTempColumns('ban', 'dateTime', ['created_at_dt']);
            $this->convert('ban', 'created_at_dt', static fn ($r) => ['created_at_dt' => $toDt($r->created_at)]);
            Schema::table('ban', fn (Blueprint $table) => $table->dropColumn('created_at'));
            Schema::table('ban', fn (Blueprint $table) => $table->renameColumn('created_at_dt', 'created_at'));
        }

        // blacklist — без индекса на created_at
        if (Schema::getColumnType('blacklist', 'created_at') !== 'datetime') {
            $this->addTempColumns('blacklist', 'dateTime', ['created_at_dt']);
            $this->convert('blacklist', 'created_at_dt', static fn ($r) => ['created_at_dt' => $toDt($r->created_at)]);
            Schema::table('blacklist', fn (Blueprint $table) => $table->dropColumn('created_at'));
            Schema::table('blacklist', fn (Blueprint $table) => $table->renameColumn('created_at_dt', 'created_at'));
        }

        // banhist — одиночный индекс created_at (dropColumn убирает его, пересоздаём)
        if (Schema::getColumnType('banhist', 'created_at') !== 'datetime') {
            $this->addTempColumns('banhist', 'dateTime', ['created_at_dt']);
            $this->convert('banhist', 'created_at_dt', static fn ($r) => ['created_at_dt' => $toDt($r->created_at)]);
            Schema::table('banhist', fn (Blueprint $table) => $table->dropColumn('created_at'));
            Schema::table('banhist', fn (Blueprint $table) => $table->renameColumn('created_at_dt', 'created_at'));
            Schema::table('banhist', fn (Blueprint $table) => $table->index('created_at'));
        }
    }

    public function down(): void
    {
        $toInt = static fn ($v) => $v ? Date::parse($v, config('app.timezone'))->getTimestamp() : null;

        if (Schema::getColumnType('ban', 'created_at') === 'datetime') {
            $this->addTempColumns('ban', 'integer', ['created_at_int']);
            $this->convert('ban', 'created_at_int', static fn ($r) => ['created_at_int' => $toInt($r->created_at)]);
            Schema::table('ban', fn (Blueprint $table) => $table->dropColumn('created_at'));
            Schema::table('ban', fn (Blueprint $table) => $table->renameColumn('created_at_int', 'created_at'));
        }

        if (Schema::getColumnType('blacklist', 'created_at') === 'datetime') {
            $this->addTempColumns('blacklist', 'integer', ['created_at_int']);
            $this->convert('blacklist', 'created_at_int', static fn ($r) => ['created_at_int' => $toInt($r->created_at)]);
            Schema::table('blacklist', fn (Blueprint $table) => $table->dropColumn('created_at'));
            Schema::table('blacklist', fn (Blueprint $table) => $table->renameColumn('created_at_int', 'created_at'));
        }

        if (Schema::getColumnType('banhist', 'created_at') === 'datetime') {
            $this->addTempColumns('banhist', 'integer', ['created_at_int']);
            $this->convert('banhist', 'created_at_int', static fn ($r) => ['created_at_int' => $toInt($r->created_at)]);
            Schema::table('banhist', fn (Blueprint $table) => $table->dropColumn('created_at'));
            Schema::table('banhist', fn (Blueprint $table) => $table->renameColumn('created_at_int', 'created_at'));
            Schema::table('banhist', fn (Blueprint $table) => $table->index('created_at'));
        }
    }
};
