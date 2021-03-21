<?php

declare(strict_types=1);

use App\Models\Setting;
use Phinx\Migration\AbstractMigration;

final class DeleteTimezoneInSettings extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up(): void
    {
        Setting::query()->where('name', 'timezone')->delete();
    }

    /**
     * Migrate Down.
     */
    public function down(): void
    {
        Setting::query()->where('name', 'timezone')->updateOrCreate([], [
            'name'  => 'timezone',
            'value' => 'Europe/Moscow',
        ]);
    }
}
