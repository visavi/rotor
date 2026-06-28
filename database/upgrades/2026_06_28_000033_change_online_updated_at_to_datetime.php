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
        DB::table($table)->select(['uid', 'updated_at'])->orderBy('uid')
            ->chunkById(5000, function ($rows) use ($table, $map) {
                DB::transaction(function () use ($rows, $table, $map) {
                    foreach ($rows as $row) {
                        DB::table($table)->where('uid', $row->uid)->update($map($row));
                    }
                });
            }, 'uid');
    }

    public function up(): void
    {
        // Свежая схема уже создаёт updated_at как datetime — конверсия не нужна
        if (Schema::getColumnType('online', 'updated_at') === 'datetime') {
            return;
        }

        $toDt = static fn ($v) => $v ? Carbon::createFromTimestamp($v, config('app.timezone'))->format('Y-m-d H:i:s') : null;

        Schema::table('online', fn (Blueprint $table) => $table->dateTime('updated_at_dt')->nullable());
        $this->convert('online', static fn ($r) => ['updated_at_dt' => $toDt($r->updated_at)]);
        Schema::table('online', fn (Blueprint $table) => $table->dropColumn('updated_at'));
        Schema::table('online', fn (Blueprint $table) => $table->renameColumn('updated_at_dt', 'updated_at'));
    }

    public function down(): void
    {
        // Колонка уже int — откатывать нечего
        if (Schema::getColumnType('online', 'updated_at') !== 'datetime') {
            return;
        }

        $toInt = static fn ($v) => $v ? Carbon::parse($v, config('app.timezone'))->getTimestamp() : null;

        Schema::table('online', fn (Blueprint $table) => $table->integer('updated_at_int')->nullable());
        $this->convert('online', static fn ($r) => ['updated_at_int' => $toInt($r->updated_at)]);
        Schema::table('online', fn (Blueprint $table) => $table->dropColumn('updated_at'));
        Schema::table('online', fn (Blueprint $table) => $table->renameColumn('updated_at_int', 'updated_at'));
    }
};
