<?php

declare(strict_types=1);

use App\Classes\BBMigrator;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        $migrator = new BBMigrator();

        DB::table('notices')
            ->orderBy('id')
            ->chunk(500, static function ($records) use ($migrator) {
                $updates = [];

                foreach ($records as $record) {
                    // [url=%placeholder%]...[/url] → %page% до общей конвертации
                    $text = preg_replace('~\[url=%[^%]+%\].+?\[/url\]~s', '%page%', $record->text);

                    $text = $migrator->convertText($text);

                    $updates[] = [
                        'id'   => $record->id,
                        'text' => $text,
                    ];
                }

                DB::transaction(static function () use ($updates) {
                    DB::table('notices')->upsert($updates, ['id'], ['text']);
                });
            });

        // @%login% → %login% (ссылка генерируется в textNotice)
        DB::table('notices')
            ->update(['text' => DB::raw("REPLACE(text, '@%login%', '%login%')")]);

        DB::table('notices')
            ->where('type', 'notify')
            ->update(['text' => '<p>Пользователь %login% упомянул вас на странице <strong>%page%</strong></p><p>%text%</p>']);
    }

    public function down(): void
    {
    }
};
