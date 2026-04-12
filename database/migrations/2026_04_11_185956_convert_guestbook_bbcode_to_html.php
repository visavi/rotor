<?php

declare(strict_types=1);

use App\Classes\BBMigrator;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Один экземпляр на всю миграцию: парсеры и кэш стикеров создаются один раз
        $migrator = new BBMigrator();

        DB::table('guestbook')
            ->orderBy('id')
            ->chunk(500, static function ($records) use ($migrator) {
                $updates = [];

                foreach ($records as $record) {
                    $updates[] = [
                        'id'    => $record->id,
                        'text'  => $migrator->convertText($record->text),
                        'reply' => $record->reply ? $migrator->convertText($record->reply) : null,
                    ];
                }

                // Один запрос на чанк вместо N отдельных UPDATE
                DB::transaction(static function () use ($updates) {
                    DB::table('guestbook')->upsert($updates, ['id'], ['text', 'reply']);
                });
            });
    }

    public function down(): void
    {
    }
};
