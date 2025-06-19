<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void
    {
        Setting::query()->where('name', 'slug_template')->updateOrCreate([], [
            'name'  => 'slug_template',
            'value' => '%id%-%slug%',
        ]);

        clearCache('settings');
    }

    public function down(): void
    {
        Setting::query()->where('name', 'slug_template')->delete();
    }
};
