<?php

declare(strict_types=1);

use App\Models\Setting;
use Phinx\Migration\AbstractMigration;

final class RemoveEditforumpointFromSettings extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up(): void
    {
        Setting::query()->where('name', 'forumloadpoints')->delete();
    }

    /**
     * Migrate Down.
     */
    public function down(): void
    {
        Setting::query()->where('name', 'editforumpoint')->updateOrCreate([], [
            'name'  => 'forumloadpoints',
            'value' => '50',
        ]);
    }
}
