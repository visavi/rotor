<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void
    {
        Setting::query()->where('name', 'image_extensions')->update([
            'name'  => 'media_extensions',
            'value' => 'jpg,jpeg,gif,png,webp,mp4,webm',
        ]);

        clearCache('settings');
    }

    public function down(): void
    {
        Setting::query()->where('name', 'media_extensions')->update([
            'name'  => 'image_extensions',
            'value' => 'jpg,jpeg,gif,png,webp',
        ]);

        clearCache('settings');
    }
};
