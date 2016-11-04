<?php

use Phinx\Migration\AbstractMigration;

class RenameFieldsInStatus extends AbstractMigration
{
    /**
     * Migrate Change.
     */
    public function change()
    {
        $table = $this->table('status');
        $table->renameColumn('status_id', 'id');
        $table->renameColumn('status_topoint', 'topoint');
        $table->renameColumn('status_point', 'point');
        $table->renameColumn('status_name', 'name');
        $table->renameColumn('status_color', 'color');
    }
}
