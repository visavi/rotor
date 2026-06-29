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
        $toDt = static fn ($v) => $v ? Date::createFromTimestamp($v, config('app.timezone'))->format('Y-m-d H:i:s') : null;

        // ban — без индекса на created_at
        if (Schema::getColumnType('ban', 'created_at') !== 'datetime') {
            Schema::table('ban', fn (Blueprint $table) => $table->dateTime('created_at_dt')->nullable());
            $this->convert('ban', static fn ($r) => ['created_at_dt' => $toDt($r->created_at)]);
            Schema::table('ban', fn (Blueprint $table) => $table->dropColumn('created_at'));
            Schema::table('ban', fn (Blueprint $table) => $table->renameColumn('created_at_dt', 'created_at'));
        }

        // blacklist — без индекса на created_at
        if (Schema::getColumnType('blacklist', 'created_at') !== 'datetime') {
            Schema::table('blacklist', fn (Blueprint $table) => $table->dateTime('created_at_dt')->nullable());
            $this->convert('blacklist', static fn ($r) => ['created_at_dt' => $toDt($r->created_at)]);
            Schema::table('blacklist', fn (Blueprint $table) => $table->dropColumn('created_at'));
            Schema::table('blacklist', fn (Blueprint $table) => $table->renameColumn('created_at_dt', 'created_at'));
        }

        // banhist — одиночный индекс created_at (dropColumn убирает его, пересоздаём)
        if (Schema::getColumnType('banhist', 'created_at') !== 'datetime') {
            Schema::table('banhist', fn (Blueprint $table) => $table->dateTime('created_at_dt')->nullable());
            $this->convert('banhist', static fn ($r) => ['created_at_dt' => $toDt($r->created_at)]);
            Schema::table('banhist', fn (Blueprint $table) => $table->dropColumn('created_at'));
            Schema::table('banhist', fn (Blueprint $table) => $table->renameColumn('created_at_dt', 'created_at'));
            Schema::table('banhist', fn (Blueprint $table) => $table->index('created_at'));
        }
    }

    public function down(): void
    {
        $toInt = static fn ($v) => $v ? Date::parse($v, config('app.timezone'))->getTimestamp() : null;

        if (Schema::getColumnType('ban', 'created_at') === 'datetime') {
            Schema::table('ban', fn (Blueprint $table) => $table->integer('created_at_int')->nullable());
            $this->convert('ban', static fn ($r) => ['created_at_int' => $toInt($r->created_at)]);
            Schema::table('ban', fn (Blueprint $table) => $table->dropColumn('created_at'));
            Schema::table('ban', fn (Blueprint $table) => $table->renameColumn('created_at_int', 'created_at'));
        }

        if (Schema::getColumnType('blacklist', 'created_at') === 'datetime') {
            Schema::table('blacklist', fn (Blueprint $table) => $table->integer('created_at_int')->nullable());
            $this->convert('blacklist', static fn ($r) => ['created_at_int' => $toInt($r->created_at)]);
            Schema::table('blacklist', fn (Blueprint $table) => $table->dropColumn('created_at'));
            Schema::table('blacklist', fn (Blueprint $table) => $table->renameColumn('created_at_int', 'created_at'));
        }

        if (Schema::getColumnType('banhist', 'created_at') === 'datetime') {
            Schema::table('banhist', fn (Blueprint $table) => $table->integer('created_at_int')->nullable());
            $this->convert('banhist', static fn ($r) => ['created_at_int' => $toInt($r->created_at)]);
            Schema::table('banhist', fn (Blueprint $table) => $table->dropColumn('created_at'));
            Schema::table('banhist', fn (Blueprint $table) => $table->renameColumn('created_at_int', 'created_at'));
            Schema::table('banhist', fn (Blueprint $table) => $table->index('created_at'));
        }
    }
};
