<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void
    {
        Setting::query()->where('name', 'boards_period')->updateOrCreate([], [
            'name'  => 'boards_period',
            'value' => 30,
        ]);

        clearCache('settings');
    }

    public function down(): void
    {
        Setting::query()->where('name', 'boards_period')->delete();
    }
};
