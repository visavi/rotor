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
    private function convert(string $table, callable $map): void
    {
        DB::table($table)->select(['id', 'created_at'])->orderBy('id')
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
        if (Schema::getColumnType('messages', 'created_at') === 'datetime') {
            return;
        }

        $toDt = static fn ($v) => $v ? Carbon::createFromTimestamp($v, config('app.timezone'))->format('Y-m-d H:i:s') : null;

        Schema::table('messages', fn (Blueprint $table) => $table->dateTime('created_at_dt')->nullable());
        $this->convert('messages', static fn ($r) => ['created_at_dt' => $toDt($r->created_at)]);
        Schema::table('messages', fn (Blueprint $table) => $table->dropColumn('created_at'));
        Schema::table('messages', fn (Blueprint $table) => $table->renameColumn('created_at_dt', 'created_at'));
    }

    public function down(): void
    {
        // Колонка уже int — откатывать нечего
        if (Schema::getColumnType('messages', 'created_at') !== 'datetime') {
            return;
        }

        $toInt = static fn ($v) => $v ? Carbon::parse($v, config('app.timezone'))->getTimestamp() : null;

        Schema::table('messages', fn (Blueprint $table) => $table->integer('created_at_int')->nullable());
        $this->convert('messages', static fn ($r) => ['created_at_int' => $toInt($r->created_at)]);
        Schema::table('messages', fn (Blueprint $table) => $table->dropColumn('created_at'));
        Schema::table('messages', fn (Blueprint $table) => $table->renameColumn('created_at_int', 'created_at'));
    }
};
