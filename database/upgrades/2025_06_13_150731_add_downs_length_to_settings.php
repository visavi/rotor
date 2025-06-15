<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void
    {
        Setting::query()->where('name', 'down_title_min')->updateOrCreate([], [
            'name'  => 'down_title_min',
            'value' => 3,
        ]);

        Setting::query()->where('name', 'down_title_max')->updateOrCreate([], [
            'name'  => 'down_title_max',
            'value' => 50,
        ]);

        Setting::query()->where('name', 'down_text_min')->updateOrCreate([], [
            'name'  => 'down_text_min',
            'value' => 50,
        ]);

        Setting::query()->where('name', 'down_text_max')->updateOrCreate([], [
            'name'  => 'down_text_max',
            'value' => 10000,
        ]);

        Setting::query()->where('name', 'down_link_min')->updateOrCreate([], [
            'name'  => 'down_link_min',
            'value' => 5,
        ]);

        Setting::query()->where('name', 'down_link_max')->updateOrCreate([], [
            'name'  => 'down_link_max',
            'value' => 100,
        ]);

        Setting::query()->where('name', 'down_category_min')->updateOrCreate([], [
            'name'  => 'down_category_min',
            'value' => 3,
        ]);

        Setting::query()->where('name', 'down_category_max')->updateOrCreate([], [
            'name'  => 'down_category_max',
            'value' => 50,
        ]);

        clearCache('settings');
    }

    public function down(): void
    {
        Setting::query()->where('name', 'down_title_min')->delete();
        Setting::query()->where('name', 'down_title_max')->delete();
        Setting::query()->where('name', 'down_text_min')->delete();
        Setting::query()->where('name', 'down_text_max')->delete();
        Setting::query()->where('name', 'down_link_min')->delete();
        Setting::query()->where('name', 'down_link_max')->delete();
        Setting::query()->where('name', 'down_category_min')->delete();
        Setting::query()->where('name', 'down_category_max')->delete();
    }
};
