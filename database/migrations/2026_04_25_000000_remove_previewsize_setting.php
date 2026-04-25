<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void
    {
        Setting::query()->where('name', 'previewsize')->delete();

        clearCache('settings');
    }

    public function down(): void
    {
        Setting::query()->where('name', 'previewsize')->updateOrCreate([], ['name' => 'previewsize', 'value' => 500]);

        clearCache('settings');
    }
};
