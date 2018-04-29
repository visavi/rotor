<?php

use Phinx\Migration\AbstractMigration;

class RenameLangInUsers extends AbstractMigration
{
    /**
     * Migrate Change.
     */
    public function change()
    {
        $table = $this->table('users');
        $table->renameColumn('lang', 'language');
    }
}
