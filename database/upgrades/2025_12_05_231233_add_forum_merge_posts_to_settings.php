<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void
    {
        Setting::query()->where('name', 'forum_merge_posts')->updateOrCreate([], [
            'name'  => 'forum_merge_posts',
            'value' => 1,
        ]);

        clearCache('settings');
    }

    public function down(): void
    {
        Setting::query()->where('name', 'forum_merge_posts')->delete();
    }
};
