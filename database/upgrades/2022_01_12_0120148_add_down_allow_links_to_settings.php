<?php

declare(strict_types=1);

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;

final class AddDownAllowLinksToSettings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Setting::query()->where('name', 'down_allow_links')->updateOrCreate([], [
            'name'  => 'down_allow_links',
            'value' => 0,
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
        Setting::query()->where('name', 'down_allow_links')->delete();
    }
}
