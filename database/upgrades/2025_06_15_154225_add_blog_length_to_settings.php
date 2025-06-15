<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void
    {
        Setting::query()->where('name', 'blog_title_min')->updateOrCreate([], [
            'name'  => 'blog_title_min',
            'value' => 3,
        ]);

        Setting::query()->where('name', 'blog_title_max')->updateOrCreate([], [
            'name'  => 'blog_title_max',
            'value' => 50,
        ]);

        Setting::query()->where('name', 'blog_text_min')->updateOrCreate([], [
            'name'  => 'blog_text_min',
            'value' => 50,
        ]);

        Setting::query()->where('name', 'blog_text_max')->updateOrCreate([], [
            'name'  => 'blog_text_max',
            'value' => 50000,
        ]);

        Setting::query()->where('name', 'blog_tag_min')->updateOrCreate([], [
            'name'  => 'blog_tag_min',
            'value' => 2,
        ]);

        Setting::query()->where('name', 'blog_tag_max')->updateOrCreate([], [
            'name'  => 'blog_tag_max',
            'value' => 30,
        ]);

        Setting::query()->where('name', 'blog_category_min')->updateOrCreate([], [
            'name'  => 'blog_category_min',
            'value' => 3,
        ]);

        Setting::query()->where('name', 'blog_category_max')->updateOrCreate([], [
            'name'  => 'blog_category_max',
            'value' => 50,
        ]);

        // Удаляем предыдущую настройку
        Setting::query()->where('name', 'maxblogpost')->delete();

        clearCache('settings');
    }

    public function down(): void
    {
        Setting::query()->where('name', 'blog_title_min')->delete();
        Setting::query()->where('name', 'blog_title_max')->delete();
        Setting::query()->where('name', 'blog_text_min')->delete();
        Setting::query()->where('name', 'blog_text_max')->delete();
        Setting::query()->where('name', 'blog_tag_min')->delete();
        Setting::query()->where('name', 'blog_tag_max')->delete();
        Setting::query()->where('name', 'blog_category_min')->delete();
        Setting::query()->where('name', 'blog_category_max')->delete();

        Setting::query()->where('name', 'maxblogpost')->updateOrCreate([], [
            'name'  => 'maxblogpost',
            'value' => 50000,
        ]);
    }
};
