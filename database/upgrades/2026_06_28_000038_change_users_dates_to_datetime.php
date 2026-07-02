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
        // Свежая схема уже создаёт колонки как datetime — конверсия не нужна
        if (Schema::getColumnType('users', 'created_at') === 'datetime') {
            return;
        }

        // 0 в timebonus = «бонус никогда не брали» → null
        $toDt = static fn ($v) => $v ? Date::createFromTimestamp($v, config('app.timezone'))->format('Y-m-d H:i:s') : null;

        $this->addTempColumns('users', 'dateTime', ['timeban_dt', 'timebonus_dt', 'created_at_dt', 'updated_at_dt']);
        $this->convert('users', 'created_at_dt', ['created_at', 'updated_at', 'timeban', 'timebonus'], static fn ($r) => [
            'created_at_dt' => $toDt($r->created_at),
            'updated_at_dt' => $toDt($r->updated_at),
            'timeban_dt'    => $toDt($r->timeban),
            'timebonus_dt'  => $toDt($r->timebonus),
        ]);
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['created_at']);
            $table->dropColumn(['created_at', 'updated_at', 'timeban', 'timebonus']);
        });
        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('timeban_dt', 'timeban');
            $table->renameColumn('timebonus_dt', 'timebonus');
            $table->renameColumn('created_at_dt', 'created_at');
            $table->renameColumn('updated_at_dt', 'updated_at');
        });
        Schema::table('users', fn (Blueprint $table) => $table->index('created_at'));
    }

    public function down(): void
    {
        // Колонки уже int — откатывать нечего
        if (Schema::getColumnType('users', 'created_at') !== 'datetime') {
            return;
        }

        $toInt = static fn ($v) => $v ? Date::parse($v, config('app.timezone'))->getTimestamp() : null;

        $this->addTempColumns('users', 'integer', ['timeban_int', 'timebonus_int', 'created_at_int', 'updated_at_int']);
        $this->convert('users', 'created_at_int', ['created_at', 'updated_at', 'timeban', 'timebonus'], static fn ($r) => [
            'created_at_int' => $toInt($r->created_at),
            'updated_at_int' => $toInt($r->updated_at),
            'timeban_int'    => $toInt($r->timeban),
            'timebonus_int'  => $toInt($r->timebonus),
        ]);
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['created_at']);
            $table->dropColumn(['created_at', 'updated_at', 'timeban', 'timebonus']);
        });
        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('timeban_int', 'timeban');
            $table->renameColumn('timebonus_int', 'timebonus');
            $table->renameColumn('created_at_int', 'created_at');
            $table->renameColumn('updated_at_int', 'updated_at');
        });
        Schema::table('users', fn (Blueprint $table) => $table->index('created_at'));
    }
};
