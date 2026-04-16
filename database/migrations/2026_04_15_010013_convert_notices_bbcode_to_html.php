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
                    $text = $migrator->convertText($record->text);

                    // BBMigrator не конвертирует [url=%placeholder%] — обрабатываем вручную
                    $text = preg_replace('~\[url=(%[^%]+%)\](.+?)\[/url\]~s', '<a href="$1">$2</a>', $text);
                    $text = preg_replace('~\[b\](.+?)\[/b\]~s', '<strong>$1</strong>', $text);

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
            ->update(['text' => '<p>Пользователь %login% упомянул вас на странице <strong><a href="%url%">%title%</a></strong></p><blockquote>%text%</blockquote>']);
    }

    public function down(): void
    {
    }
};
