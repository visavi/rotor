<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void
    {
        Setting::query()->where('name', 'board_title_min')->updateOrCreate([], [
            'name'  => 'board_title_min',
            'value' => 3,
        ]);

        Setting::query()->where('name', 'board_title_max')->updateOrCreate([], [
            'name'  => 'board_title_max',
            'value' => 50,
        ]);

        Setting::query()->where('name', 'board_text_min')->updateOrCreate([], [
            'name'  => 'board_text_min',
            'value' => 10,
        ]);

        Setting::query()->where('name', 'board_text_max')->updateOrCreate([], [
            'name'  => 'board_text_max',
            'value' => 5000,
        ]);

        Setting::query()->where('name', 'board_category_min')->updateOrCreate([], [
            'name'  => 'board_category_min',
            'value' => 3,
        ]);

        Setting::query()->where('name', 'board_category_max')->updateOrCreate([], [
            'name'  => 'board_category_max',
            'value' => 50,
        ]);

        clearCache('settings');
    }

    public function down(): void
    {
        Setting::query()->where('name', 'board_title_min')->delete();
        Setting::query()->where('name', 'board_title_max')->delete();
        Setting::query()->where('name', 'board_text_min')->delete();
        Setting::query()->where('name', 'board_text_max')->delete();
        Setting::query()->where('name', 'board_category_min')->delete();
        Setting::query()->where('name', 'board_category_max')->delete();
    }
};
