<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void
    {
        Setting::query()->where('name', 'image_extensions')->updateOrCreate([], [
            'name'  => 'image_extensions',
            'value' => 'jpg,jpeg,gif,png,webp',
        ]);

        clearCache('settings');
    }

    public function down(): void
    {
        Setting::query()->where('name', 'image_extensions')->delete();
    }
};
