<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void
    {
        Setting::query()->where('name', 'boards_per_page')->updateOrCreate([], [
            'name'  => 'boards_per_page',
            'value' => 10,
        ]);

        clearCache('settings');
    }

    public function down(): void
    {
        Setting::query()->where('name', 'boards_per_page')->delete();
    }
};
