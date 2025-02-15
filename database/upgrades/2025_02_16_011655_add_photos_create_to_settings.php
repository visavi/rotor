<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void
    {
        Setting::query()->where('name', 'photos_create')->updateOrCreate([], [
            'name'  => 'photos_create',
            'value' => 1,
        ]);

        clearCache('settings');
    }

    public function down(): void
    {
        Setting::query()->where('name', 'photos_create')->delete();
    }
};
