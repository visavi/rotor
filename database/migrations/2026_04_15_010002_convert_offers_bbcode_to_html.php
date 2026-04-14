<?php

declare(strict_types=1);

use App\Classes\BBMigrator;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        $migrator = new BBMigrator();

        DB::table('offers')
            ->orderBy('id')
            ->chunk(500, static function ($records) use ($migrator) {
                $updates = [];

                foreach ($records as $record) {
                    $updates[] = [
                        'id'    => $record->id,
                        'text'  => $migrator->convertText($record->text),
                        'reply' => $migrator->convertText($record->reply),
                    ];
                }

                DB::transaction(static function () use ($updates) {
                    DB::table('offers')->upsert($updates, ['id'], ['text', 'reply']);
                });
            });
    }

    public function down(): void
    {
    }
};
