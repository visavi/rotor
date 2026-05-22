<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $defaults = [
            ['name' => 'forumpost',             'value' => 10],
            ['name' => 'forumtem',              'value' => 10],
            ['name' => 'forum_title_min',       'value' => 3],
            ['name' => 'forum_title_max',       'value' => 50],
            ['name' => 'forum_note_min',        'value' => 0],
            ['name' => 'forum_note_max',        'value' => 250],
            ['name' => 'forum_text_min',        'value' => 5],
            ['name' => 'forum_text_max',        'value' => 5000],
            ['name' => 'forum_category_min',    'value' => 3],
            ['name' => 'forum_category_max',    'value' => 50],
            ['name' => 'forum_description_min', 'value' => 0],
            ['name' => 'forum_description_max', 'value' => 100],
            ['name' => 'forum_point',           'value' => 1],
            ['name' => 'forum_money',           'value' => 50],
            ['name' => 'forum_merge_posts',     'value' => 1],
            ['name' => 'editforumpoint',        'value' => 300],
            ['name' => 'vote_title_min',        'value' => 5],
            ['name' => 'vote_title_max',        'value' => 50],
            ['name' => 'vote_answer_min',       'value' => 1],
            ['name' => 'vote_answer_max',       'value' => 50],
        ];

        foreach ($defaults as $setting) {
            Setting::query()->insertOrIgnore($setting);
        }
    }

    public function down(): void {}
};
