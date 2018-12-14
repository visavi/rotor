<?php

use Phinx\Migration\AbstractMigration;

class AddCategoryIdToSmiles extends AbstractMigration
{
    /**
     * Migrate Change.
     */
    public function change(): void
    {
        $table = $this->table('smiles');
        $table->addColumn('category_id', 'integer', ['default' => 0, 'after' => 'id'])
            ->update();
    }
}
