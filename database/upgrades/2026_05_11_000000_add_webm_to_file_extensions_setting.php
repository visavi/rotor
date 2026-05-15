<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void
    {
        $setting = Setting::query()->where('name', 'file_extensions')->first();

        if ($setting && ! str_contains($setting->value, 'webm')) {
            Setting::query()
                ->where('name', 'file_extensions')
                ->update(['value' => $setting->value . ',webm']);
        }

        clearCache('settings');
    }

    public function down(): void
    {
        $setting = Setting::query()->where('name', 'file_extensions')->first();

        if ($setting) {
            Setting::query()
                ->where('name', 'file_extensions')
                ->update(['value' => str_replace(',webm', '', $setting->value)]);
        }

        clearCache('settings');
    }
};
