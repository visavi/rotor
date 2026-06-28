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
        // Свежая схема уже создаёт created_at как datetime — конверсия не нужна
        if (Schema::getColumnType('files', 'created_at') === 'datetime') {
            return;
        }

        $toDt = static fn ($v) => $v ? Carbon::createFromTimestamp($v, config('app.timezone'))->format('Y-m-d H:i:s') : null;

        Schema::table('files', fn (Blueprint $table) => $table->dateTime('created_at_dt')->nullable());
        $this->convert('files', ['created_at'], static fn ($r) => ['created_at_dt' => $toDt($r->created_at)]);
        Schema::table('files', function (Blueprint $table) {
            $table->dropIndex(['created_at']);
            $table->dropColumn('created_at');
        });
        Schema::table('files', fn (Blueprint $table) => $table->renameColumn('created_at_dt', 'created_at'));
        Schema::table('files', fn (Blueprint $table) => $table->index('created_at'));
    }

    public function down(): void
    {
        // Колонка уже int — откатывать нечего
        if (Schema::getColumnType('files', 'created_at') !== 'datetime') {
            return;
        }

        $toInt = static fn ($v) => $v ? Carbon::parse($v, config('app.timezone'))->getTimestamp() : null;

        Schema::table('files', fn (Blueprint $table) => $table->integer('created_at_int')->nullable());
        $this->convert('files', ['created_at'], static fn ($r) => ['created_at_int' => $toInt($r->created_at)]);
        Schema::table('files', function (Blueprint $table) {
            $table->dropIndex(['created_at']);
            $table->dropColumn('created_at');
        });
        Schema::table('files', fn (Blueprint $table) => $table->renameColumn('created_at_int', 'created_at'));
        Schema::table('files', fn (Blueprint $table) => $table->index('created_at'));
    }
};
