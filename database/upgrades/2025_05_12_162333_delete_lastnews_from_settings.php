<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void
    {
        Setting::query()->where('name', 'lastnews')->delete();

        clearCache('settings');
    }

    public function down(): void
    {
        Setting::query()->where('name', 'lastnews')->updateOrCreate([], [
            'name'  => 'lastnews',
            'value' => 5,
        ]);
    }
};
