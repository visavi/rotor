<?php

declare(strict_types=1);

use App\Classes\BBMigrator;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('guestbook', static function (Blueprint $table) {
            $table->text('text2')->nullable()->after('text');
        });

        // Один экземпляр на всю миграцию: парсеры и кэш стикеров создаются один раз
        $migrator = new BBMigrator();

        DB::table('guestbook')
            ->orderBy('id')
            ->chunk(500, static function ($records) use ($migrator) {
                $updates = [];

                foreach ($records as $record) {
                    $updates[] = [
                        'id'    => $record->id,
                        'text2' => $migrator->convertText($record->text),
                    ];
                }

                // Один запрос на чанк вместо N отдельных UPDATE
                DB::transaction(static function () use ($updates) {
                    DB::table('guestbook')->upsert($updates, ['id'], ['text2']);
                });
            });
    }

    public function down(): void
    {
        Schema::table('guestbook', static function (Blueprint $table) {
            $table->dropColumn('text2');
        });
    }
};
