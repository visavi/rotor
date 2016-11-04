<?php

use Phinx\Migration\AbstractMigration;

class RenameFieldsInRules extends AbstractMigration
{
    /**
     * Migrate Change.
     */
    public function change()
    {
        $table = $this->table('rules');
        $table->renameColumn('rules_id', 'id');
        $table->renameColumn('rules_text', 'text');
        $table->renameColumn('rules_time', 'time');
    }
}
