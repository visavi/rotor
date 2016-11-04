<?php

use Phinx\Migration\AbstractMigration;

class RenameFieldsInRekuser extends AbstractMigration
{
    /**
     * Migrate Change.
     */
    public function change()
    {
        $table = $this->table('rekuser');
        $table->renameColumn('rek_id', 'id');
        $table->renameColumn('rek_site', 'site');
        $table->renameColumn('rek_name', 'name');
        $table->renameColumn('rek_color', 'color');
        $table->renameColumn('rek_bold', 'bold');
        $table->renameColumn('rek_user', 'user');
        $table->renameColumn('rek_time', 'time');
    }
}
