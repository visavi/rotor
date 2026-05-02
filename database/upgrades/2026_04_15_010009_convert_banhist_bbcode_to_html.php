<?php

declare(strict_types=1);

use App\Classes\BBMigrator;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        $migrator = new BBMigrator();

        DB::table('banhist')
            ->whereNotNull('reason')
            ->orderBy('id')
            ->chunk(500, static function ($records) use ($migrator) {
                $updates = [];

                foreach ($records as $record) {
                    $updates[] = [
                        'id'     => $record->id,
                        'reason' => $migrator->convertText($record->reason),
                    ];
                }

                DB::transaction(static function () use ($updates) {
                    DB::table('banhist')->upsert($updates, ['id'], ['reason']);
                });
            });
    }

    public function down(): void
    {
    }
};
