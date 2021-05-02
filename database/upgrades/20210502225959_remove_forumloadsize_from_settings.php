<?php

declare(strict_types=1);

use App\Models\Setting;
use Phinx\Migration\AbstractMigration;

final class RemoveForumloadsizeFromSettings extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up(): void
    {
        Setting::query()->where('name', 'forumloadsize')->delete();
        Setting::query()->where('name', 'forumextload')->update(['name' => 'file_extensions']);
    }

    /**
     * Migrate Down.
     */
    public function down(): void
    {
        Setting::query()->where('name', 'forumloadsize')->updateOrCreate([], [
            'name'  => 'forumloadsize',
            'value' => '1048576',
        ]);

        Setting::query()->where('name', 'file_extensions')->update(['name' => 'forumextload']);
    }
}
