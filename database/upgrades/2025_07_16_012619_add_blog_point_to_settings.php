<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void
    {
        Setting::query()->where('name', 'blog_point')->updateOrCreate([], [
            'name'  => 'blog_point',
            'value' => 5,
        ]);

        Setting::query()->where('name', 'blog_money')->updateOrCreate([], [
            'name'  => 'blog_money',
            'value' => 500,
        ]);

        Setting::query()->where('name', 'blogvotepoint')->delete();

        clearCache('settings');
    }

    public function down(): void
    {
        Setting::query()->where('name', 'blog_point')->delete();
        Setting::query()->where('name', 'blog_money')->delete();

        Setting::query()->where('name', 'blogvotepoint')->updateOrCreate([], [
            'name'  => 'blogvotepoint',
            'value' => 50,
        ]);
    }
};
