<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void
    {
        Setting::query()->where('name', 'offer_title_min')->updateOrCreate([], [
            'name'  => 'offer_title_min',
            'value' => 3,
        ]);

        Setting::query()->where('name', 'offer_title_max')->updateOrCreate([], [
            'name'  => 'offer_title_max',
            'value' => 50,
        ]);

        Setting::query()->where('name', 'offer_text_min')->updateOrCreate([], [
            'name'  => 'offer_text_min',
            'value' => 5,
        ]);

        Setting::query()->where('name', 'offer_text_max')->updateOrCreate([], [
            'name'  => 'offer_text_max',
            'value' => 1000,
        ]);

        Setting::query()->where('name', 'offer_reply_min')->updateOrCreate([], [
            'name'  => 'offer_reply_min',
            'value' => 5,
        ]);

        Setting::query()->where('name', 'offer_reply_max')->updateOrCreate([], [
            'name'  => 'offer_reply_max',
            'value' => 3000,
        ]);

        clearCache('settings');
    }

    public function down(): void
    {
        Setting::query()->where('name', 'offer_title_min')->delete();
        Setting::query()->where('name', 'offer_title_max')->delete();
        Setting::query()->where('name', 'offer_text_min')->delete();
        Setting::query()->where('name', 'offer_text_max')->delete();
        Setting::query()->where('name', 'offer_reply_min')->delete();
        Setting::query()->where('name', 'offer_reply_max')->delete();
    }
};
