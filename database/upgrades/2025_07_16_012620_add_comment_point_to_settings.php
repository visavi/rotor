<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void
    {
        Setting::query()->where('name', 'comment_point')->updateOrCreate([], [
            'name'  => 'comment_point',
            'value' => 1,
        ]);

        Setting::query()->where('name', 'comment_money')->updateOrCreate([], [
            'name'  => 'comment_money',
            'value' => 50,
        ]);

        clearCache('settings');
    }

    public function down(): void
    {
        Setting::query()->where('name', 'comment_point')->delete();
        Setting::query()->where('name', 'comment_money')->delete();
    }
};
