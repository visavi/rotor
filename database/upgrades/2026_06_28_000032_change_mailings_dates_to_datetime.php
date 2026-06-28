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
        if (Schema::getColumnType('mailings', 'created_at') === 'datetime') {
            return;
        }

        $toDt = static fn ($v) => $v ? Carbon::createFromTimestamp($v, config('app.timezone'))->format('Y-m-d H:i:s') : null;

        Schema::table('mailings', function (Blueprint $table) {
            $table->dateTime('created_at_dt')->nullable();
            $table->dateTime('sent_at_dt')->nullable();
        });
        $this->convert('mailings', ['created_at', 'sent_at'], static fn ($r) => [
            'created_at_dt' => $toDt($r->created_at),
            'sent_at_dt'    => $toDt($r->sent_at),
        ]);
        Schema::table('mailings', function (Blueprint $table) {
            $table->dropColumn(['created_at', 'sent_at']);
        });
        Schema::table('mailings', function (Blueprint $table) {
            $table->renameColumn('created_at_dt', 'created_at');
            $table->renameColumn('sent_at_dt', 'sent_at');
        });
    }

    public function down(): void
    {
        // Колонка уже int — откатывать нечего
        if (Schema::getColumnType('mailings', 'created_at') !== 'datetime') {
            return;
        }

        $toInt = static fn ($v) => $v ? Carbon::parse($v, config('app.timezone'))->getTimestamp() : null;

        Schema::table('mailings', function (Blueprint $table) {
            $table->integer('created_at_int')->nullable();
            $table->integer('sent_at_int')->nullable();
        });
        $this->convert('mailings', ['created_at', 'sent_at'], static fn ($r) => [
            'created_at_int' => $toInt($r->created_at),
            'sent_at_int'    => $toInt($r->sent_at),
        ]);
        Schema::table('mailings', function (Blueprint $table) {
            $table->dropColumn(['created_at', 'sent_at']);
        });
        Schema::table('mailings', function (Blueprint $table) {
            $table->renameColumn('created_at_int', 'created_at');
            $table->renameColumn('sent_at_int', 'sent_at');
        });
    }
};
