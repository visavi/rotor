<?php

use Phinx\Migration\AbstractMigration;

class ChangeCategoryIdInStickers extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up(): void
    {
        $table = $this->table('stickers');
        $table
            ->changeColumn('category_id', 'integer')
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down(): void
    {
        $table = $this->table('stickers');
        $table
            ->changeColumn('category_id', 'integer', ['default' => 1])
            ->save();
    }
}
