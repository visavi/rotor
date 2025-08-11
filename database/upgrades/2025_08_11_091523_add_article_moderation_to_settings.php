<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void
    {
        Setting::query()->where('name', 'article_moderation')->updateOrCreate([], [
            'name'  => 'article_moderation',
            'value' => 0,
        ]);

        clearCache('settings');
    }

    public function down(): void
    {
        Setting::query()->where('name', 'article_moderation')->delete();
    }
};
