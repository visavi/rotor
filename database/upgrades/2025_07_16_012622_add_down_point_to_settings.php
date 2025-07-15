<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void
    {
        Setting::query()->where('name', 'down_point')->updateOrCreate([], [
            'name'  => 'down_point',
            'value' => 5,
        ]);

        Setting::query()->where('name', 'down_money')->updateOrCreate([], [
            'name'  => 'down_money',
            'value' => 500,
        ]);

        clearCache('settings');
    }

    public function down(): void
    {
        Setting::query()->where('name', 'down_point')->delete();
        Setting::query()->where('name', 'down_money')->delete();
    }
};
