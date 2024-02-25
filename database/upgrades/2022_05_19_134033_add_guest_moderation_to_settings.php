<?php

declare(strict_types=1);

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Setting::query()->where('name', 'guest_moderation')->updateOrCreate([], [
            'name'  => 'guest_moderation',
            'value' => 0,
        ]);

        clearCache('settings');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Setting::query()->where('name', 'guest_moderation')->delete();
    }
};
