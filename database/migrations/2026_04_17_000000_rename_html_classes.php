<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    private array $replacements = [
        'class="block-code"'    => 'class="code"',
        'class="block-image"'   => 'class="image"',
        'class="block-video"'   => 'class="video"',
        'class="block-spoiler"' => 'class="spoiler"',
        'class="block-hidden"'  => 'class="hidden"',
        'class="mention"'       => 'class="user"',
        ' target="_blank"'      => '',
        ' rel="nofollow"'       => '',
    ];

    private array $tables = [
        'guestbook' => ['text', 'reply'],
        'news'      => ['text'],
        'comments'  => ['text'],
        'articles'  => ['text'],
        'downs'     => ['text'],
        'offers'    => ['text', 'reply'],
        'items'     => ['text'],
        'photos'    => ['text'],
        'votes'     => ['description'],
        'users'     => ['info'],
    ];

    public function up(): void
    {
        foreach ($this->tables as $table => $columns) {
            foreach ($columns as $column) {
                $expr = $column;
                foreach ($this->replacements as $from => $to) {
                    $expr = "REPLACE({$expr}, ?, ?)";
                }
                $bindings = array_merge(...array_map(
                    static fn ($from, $to) => [$from, $to],
                    array_keys($this->replacements),
                    array_values($this->replacements),
                ));
                DB::statement("UPDATE `{$table}` SET `{$column}` = {$expr} WHERE `{$column}` IS NOT NULL", $bindings);
            }
        }
    }
};
