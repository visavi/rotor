<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Setting::query()->where('name', 'invite_days')->updateOrCreate([], [
            'name'  => 'invite_days',
            'value' => 30,
        ]);

        Setting::query()->where('name', 'invite_rating')->updateOrCreate([], [
            'name'  => 'invite_rating',
            'value' => 10,
        ]);

        Setting::query()->where('name', 'invite_count')->updateOrCreate([], [
            'name'  => 'invite_count',
            'value' => 3,
        ]);

        clearCache('settings');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Setting::query()->where('name', 'invite_days')->delete();
        Setting::query()->where('name', 'invite_rating')->delete();
        Setting::query()->where('name', 'invite_count')->delete();
    }
};
