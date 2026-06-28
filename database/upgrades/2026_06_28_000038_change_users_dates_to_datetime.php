<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Чанковая конверсия с транзакцией на чанк: createFromTimestamp/parse сохраняют
     * историческую таймзону (старый DST), а батч-коммит убирает fsync-на-строку.
     */
    private function convert(string $table, array $cols, callable $map): void
    {
        DB::table($table)->select(array_merge(['id'], $cols))->orderBy('id')
            ->chunkById(5000, function ($rows) use ($table, $map) {
                DB::transaction(function () use ($rows, $table, $map) {
                    foreach ($rows as $row) {
                        DB::table($table)->where('id', $row->id)->update($map($row));
                    }
                });
            });
    }

    public function up(): void
    {
        // Свежая схема уже создаёт колонки как datetime — конверсия не нужна
        if (Schema::getColumnType('users', 'created_at') === 'datetime') {
            return;
        }

        // 0 в timebonus = «бонус никогда не брали» → null
        $toDt = static fn ($v) => $v ? Carbon::createFromTimestamp($v, config('app.timezone'))->format('Y-m-d H:i:s') : null;

        Schema::table('users', function (Blueprint $table) {
            $table->dateTime('timeban_dt')->nullable();
            $table->dateTime('timebonus_dt')->nullable();
            $table->dateTime('created_at_dt')->nullable();
            $table->dateTime('updated_at_dt')->nullable();
        });
        $this->convert('users', ['created_at', 'updated_at', 'timeban', 'timebonus'], static fn ($r) => [
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

        $toInt = static fn ($v) => $v ? Carbon::parse($v, config('app.timezone'))->getTimestamp() : null;

        Schema::table('users', function (Blueprint $table) {
            $table->integer('timeban_int')->nullable();
            $table->integer('timebonus_int')->nullable();
            $table->integer('created_at_int')->nullable();
            $table->integer('updated_at_int')->nullable();
        });
        $this->convert('users', ['created_at', 'updated_at', 'timeban', 'timebonus'], static fn ($r) => [
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
