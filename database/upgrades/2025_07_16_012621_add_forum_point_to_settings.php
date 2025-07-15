<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void
    {
        Setting::query()->where('name', 'forum_point')->updateOrCreate([], [
            'name'  => 'forum_point',
            'value' => 1,
        ]);

        Setting::query()->where('name', 'forum_money')->updateOrCreate([], [
            'name'  => 'forum_money',
            'value' => 50,
        ]);

        clearCache('settings');
    }

    public function down(): void
    {
        Setting::query()->where('name', 'forum_point')->delete();
        Setting::query()->where('name', 'forum_money')->delete();
    }
};
