<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void
    {
        Setting::query()->where('name', 'forum_title_min')->updateOrCreate([], [
            'name'  => 'forum_title_min',
            'value' => 3,
        ]);

        Setting::query()->where('name', 'forum_title_max')->updateOrCreate([], [
            'name'  => 'forum_title_max',
            'value' => 50,
        ]);

        Setting::query()->where('name', 'forum_note_min')->updateOrCreate([], [
            'name'  => 'forum_note_min',
            'value' => 0,
        ]);

        Setting::query()->where('name', 'forum_note_max')->updateOrCreate([], [
            'name'  => 'forum_note_max',
            'value' => 250,
        ]);

        Setting::query()->where('name', 'forum_text_min')->updateOrCreate([], [
            'name'  => 'forum_text_min',
            'value' => 5,
        ]);

        Setting::query()->where('name', 'forum_text_max')->updateOrCreate([], [
            'name'  => 'forum_text_max',
            'value' => 5000,
        ]);

        Setting::query()->where('name', 'forum_category_min')->updateOrCreate([], [
            'name'  => 'forum_category_min',
            'value' => 3,
        ]);

        Setting::query()->where('name', 'forum_category_max')->updateOrCreate([], [
            'name'  => 'forum_category_max',
            'value' => 50,
        ]);

        Setting::query()->where('name', 'forum_description_min')->updateOrCreate([], [
            'name'  => 'forum_description_min',
            'value' => 0,
        ]);

        Setting::query()->where('name', 'forum_description_max')->updateOrCreate([], [
            'name'  => 'forum_description_max',
            'value' => 100,
        ]);

        // Удаляем предыдущую настройку
        Setting::query()->where('name', 'forumtextlength')->delete();

        clearCache('settings');
    }

    public function down(): void
    {
        Setting::query()->where('name', 'forum_title_min')->delete();
        Setting::query()->where('name', 'forum_title_max')->delete();
        Setting::query()->where('name', 'forum_note_min')->delete();
        Setting::query()->where('name', 'forum_note_max')->delete();
        Setting::query()->where('name', 'forum_text_min')->delete();
        Setting::query()->where('name', 'forum_text_max')->delete();
        Setting::query()->where('name', 'forum_category_min')->delete();
        Setting::query()->where('name', 'forum_category_max')->delete();
        Setting::query()->where('name', 'forum_description_min')->delete();
        Setting::query()->where('name', 'forum_description_max')->delete();

        Setting::query()->where('name', 'forumtextlength')->updateOrCreate([], [
            'name'  => 'forumtextlength',
            'value' => 3000,
        ]);
    }
};
