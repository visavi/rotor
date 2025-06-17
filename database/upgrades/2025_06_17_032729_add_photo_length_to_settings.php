<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void
    {
        Setting::query()->where('name', 'photo_title_min')->updateOrCreate([], [
            'name'  => 'photo_title_min',
            'value' => 3,
        ]);

        Setting::query()->where('name', 'photo_title_max')->updateOrCreate([], [
            'name'  => 'photo_title_max',
            'value' => 50,
        ]);

        Setting::query()->where('name', 'photo_text_min')->updateOrCreate([], [
            'name'  => 'photo_text_min',
            'value' => 0,
        ]);

        Setting::query()->where('name', 'photo_text_max')->updateOrCreate([], [
            'name'  => 'photo_text_max',
            'value' => 1000,
        ]);

        clearCache('settings');
    }

    public function down(): void
    {
        Setting::query()->where('name', 'photo_title_min')->delete();
        Setting::query()->where('name', 'photo_title_max')->delete();
        Setting::query()->where('name', 'photo_text_min')->delete();
        Setting::query()->where('name', 'photo_text_max')->delete();
    }
};
