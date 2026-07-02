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
     * feeds конвертируем (не truncate) — команды переиндексации ленты нет.
     */
    private function convert(string $table, string $resumeCol, array $cols, callable $map): void
    {
        DB::table($table)->select(array_merge(['id'], $cols))
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
        // Свежая схема уже создаёт created_at как datetime — конверсия не нужна
        if (Schema::getColumnType('feeds', 'created_at') === 'datetime') {
            return;
        }

        $toDt = static fn ($v) => $v ? Date::createFromTimestamp($v, config('app.timezone'))->format('Y-m-d H:i:s') : null;

        $this->addTempColumns('feeds', 'dateTime', ['created_at_dt']);
        $this->convert('feeds', 'created_at_dt', ['created_at'], static fn ($r) => ['created_at_dt' => $toDt($r->created_at)]);
        Schema::table('feeds', function (Blueprint $table) {
            $table->dropIndex(['relate_type', 'created_at']);
            $table->dropIndex(['created_at']);
            $table->dropColumn('created_at');
        });
        Schema::table('feeds', fn (Blueprint $table) => $table->renameColumn('created_at_dt', 'created_at'));
        Schema::table('feeds', function (Blueprint $table) {
            $table->index('created_at');
            $table->index(['relate_type', 'created_at']);
        });
    }

    public function down(): void
    {
        // Колонка уже int — откатывать нечего
        if (Schema::getColumnType('feeds', 'created_at') !== 'datetime') {
            return;
        }

        $toInt = static fn ($v) => $v ? Date::parse($v, config('app.timezone'))->getTimestamp() : null;

        Schema::table('feeds', fn (Blueprint $table) => $table->unsignedInteger('created_at_int')->nullable());
        $this->convert('feeds', 'created_at_int', ['created_at'], static fn ($r) => ['created_at_int' => $toInt($r->created_at)]);
        Schema::table('feeds', function (Blueprint $table) {
            $table->dropIndex(['relate_type', 'created_at']);
            $table->dropIndex(['created_at']);
            $table->dropColumn('created_at');
        });
        Schema::table('feeds', fn (Blueprint $table) => $table->renameColumn('created_at_int', 'created_at'));
        Schema::table('feeds', function (Blueprint $table) {
            $table->index('created_at');
            $table->index(['relate_type', 'created_at']);
        });
    }
};
