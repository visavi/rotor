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
        // Свежая схема уже создаёт created_at как datetime — конверсия не нужна
        if (Schema::getColumnType('comments', 'created_at') === 'datetime') {
            return;
        }

        $toDt = static fn ($v) => $v ? Date::createFromTimestamp($v, config('app.timezone'))->format('Y-m-d H:i:s') : null;

        Schema::table('comments', function (Blueprint $table) {
            $table->dateTime('created_at_dt')->nullable();
            $table->dateTime('deleted_at_dt')->nullable();
        });
        $this->convert('comments', ['created_at', 'deleted_at'], static fn ($r) => [
            'created_at_dt' => $toDt($r->created_at),
            'deleted_at_dt' => $toDt($r->deleted_at),
        ]);
        Schema::table('comments', function (Blueprint $table) {
            $table->dropIndex(['rating', 'created_at']);
            $table->dropIndex(['created_at']);
            $table->dropColumn(['created_at', 'deleted_at']);
        });
        Schema::table('comments', function (Blueprint $table) {
            $table->renameColumn('created_at_dt', 'created_at');
            $table->renameColumn('deleted_at_dt', 'deleted_at');
        });
        Schema::table('comments', function (Blueprint $table) {
            $table->index('created_at');
            $table->index(['rating', 'created_at']);
        });
    }

    public function down(): void
    {
        // Колонка уже int — откатывать нечего
        if (Schema::getColumnType('comments', 'created_at') !== 'datetime') {
            return;
        }

        $toInt = static fn ($v) => $v ? Date::parse($v, config('app.timezone'))->getTimestamp() : null;

        Schema::table('comments', function (Blueprint $table) {
            $table->integer('created_at_int')->nullable();
            $table->unsignedInteger('deleted_at_int')->nullable();
        });
        $this->convert('comments', ['created_at', 'deleted_at'], static fn ($r) => [
            'created_at_int' => $toInt($r->created_at),
            'deleted_at_int' => $toInt($r->deleted_at),
        ]);
        Schema::table('comments', function (Blueprint $table) {
            $table->dropIndex(['rating', 'created_at']);
            $table->dropIndex(['created_at']);
            $table->dropColumn(['created_at', 'deleted_at']);
        });
        Schema::table('comments', function (Blueprint $table) {
            $table->renameColumn('created_at_int', 'created_at');
            $table->renameColumn('deleted_at_int', 'deleted_at');
        });
        Schema::table('comments', function (Blueprint $table) {
            $table->index('created_at');
            $table->index(['rating', 'created_at']);
        });
    }
};
