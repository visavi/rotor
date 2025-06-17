<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void
    {
        Setting::query()->where('name', 'comment_text_min')->updateOrCreate([], [
            'name'  => 'comment_text_min',
            'value' => 5,
        ]);

        Setting::query()->where('name', 'comment_text_max')->updateOrCreate([], [
            'name'  => 'comment_text_max',
            'value' => 1000,
        ]);

        // Удаляем предыдущую настройку
        Setting::query()->where('name', 'comment_length')->delete();

        clearCache('settings');
    }

    public function down(): void
    {
        Setting::query()->where('name', 'comment_text_min')->delete();
        Setting::query()->where('name', 'comment_text_max')->delete();

        Setting::query()->where('name', 'comment_length')->updateOrCreate([], [
            'name'  => 'comment_length',
            'value' => 1000,
        ]);
    }
};
