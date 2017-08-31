<?php

use Phinx\Migration\AbstractMigration;

class RenameOptionInVoteanswer extends AbstractMigration
{
    /**
     * Migrate Change.
     */
    public function change()
    {
        $table = $this->table('voteanswer');
        $table->renameColumn('option', 'answer');
    }
}
