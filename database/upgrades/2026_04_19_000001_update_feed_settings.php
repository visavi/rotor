<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void
    {
        Setting::query()->where('name', 'feed_cache_time')->updateOrCreate([], [
            'name'  => 'feed_cache_time',
            'value' => 300,
        ]);

        Setting::query()->whereIn('name', ['feed_total', 'feed_last_record'])->delete();

        clearCache('settings');
    }

    public function down(): void
    {
        Setting::query()->where('name', 'feed_cache_time')->delete();

        Setting::query()->where('name', 'feed_total')->updateOrCreate([], ['name' => 'feed_total', 'value' => 100]);
        Setting::query()->where('name', 'feed_last_record')->updateOrCreate([], ['name' => 'feed_last_record', 'value' => 20]);

        clearCache('settings');
    }
};
