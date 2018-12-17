<?php

use Phinx\Migration\AbstractMigration;

class AddCategoryIdToStickers extends AbstractMigration
{
    /**
     * Migrate Change.
     */
    public function change(): void
    {
        $table = $this->table('stickers');
        $table->addColumn('category_id', 'integer', ['default' => 0, 'after' => 'id'])
            ->update();
    }
}
