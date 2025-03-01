<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void
    {
        Setting::query()->where('name', 'feed_offers_show')->updateOrCreate([], [
            'name'  => 'feed_offers_show',
            'value' => 1,
        ]);

        Setting::query()->where('name', 'feed_offers_rating')->updateOrCreate([], [
            'name'  => 'feed_offers_rating',
            'value' => -5,
        ]);

        clearCache('settings');
    }

    public function down(): void
    {
        Setting::query()->where('name', 'feed_offers_show')->delete();
    }
};
