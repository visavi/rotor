<?php

declare(strict_types=1);

use App\Models\Setting;
use Phinx\Migration\AbstractMigration;

final class AddDownGuestDownloadToSettings extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up(): void
    {
        Setting::query()->where('name', 'down_guest_download')->updateOrCreate([], [
            'name'  => 'down_guest_download',
            'value' => '1',
        ]);
    }

    /**
     * Migrate Down.
     */
    public function down(): void
    {
        Setting::query()->where('name', 'down_guest_download')->delete();
    }
}
