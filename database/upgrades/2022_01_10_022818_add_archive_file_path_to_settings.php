<?php

declare(strict_types=1);

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;

final class AddArchiveFilePathToSettings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Setting::query()->where('name', 'archive_file_path')->updateOrCreate([], [
            'name'  => 'archive_file_path',
            'value' => '',
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
        Setting::query()->where('name', 'archive_file_path')->delete();
    }
}
