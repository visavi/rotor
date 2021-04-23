<?php

declare(strict_types=1);

use App\Models\Setting;
use Phinx\Migration\AbstractMigration;

final class AddEditcolorpointToSettings extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up(): void
    {
        Setting::query()->where('name', 'editcolorpoint')->updateOrCreate([], [
            'name'  => 'editcolorpoint',
            'value' => '500',
        ]);

        Setting::query()->where('name', 'editcolormoney')->updateOrCreate([], [
            'name'  => 'editcolormoney',
            'value' => '5000',
        ]);
    }

    /**
     * Migrate Down.
     */
    public function down(): void
    {
        Setting::query()->where('name', 'editcolorpoint')->delete();
        Setting::query()->where('name', 'editcolormoney')->delete();
    }
}
