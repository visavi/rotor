<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void
    {
        Setting::query()->where('name', 'boards_create')->updateOrCreate([], [
            'name'  => 'boards_create',
            'value' => 1,
        ]);

        clearCache('settings');
    }

    public function down(): void
    {
        Setting::query()->where('name', 'boards_create')->delete();
    }
};
