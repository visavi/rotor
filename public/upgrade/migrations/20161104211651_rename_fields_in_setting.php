<?php

use Phinx\Migration\AbstractMigration;

class RenameFieldsInSetting extends AbstractMigration
{
    /**
     * Migrate Change.
     */
    public function change()
    {
        $table = $this->table('setting');
        $table->renameColumn('setting_name', 'name');
        $table->renameColumn('setting_value', 'value');
    }
}
