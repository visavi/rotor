<?php

use Phinx\Migration\AbstractMigration;

class RenameFieldsInDowns2 extends AbstractMigration
{
    /**
     * Migrate Change.
     */
    public function change()
    {
        $table = $this->table('downs');
        $table->renameColumn('cats_id', 'category_id');
        $table->renameColumn('raiting', 'rating');
    }
}
