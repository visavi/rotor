<?php

use Phinx\Migration\AbstractMigration;

class RenameFieldsInVote extends AbstractMigration
{
    /**
     * Migrate Change.
     */
    public function change()
    {
        $table = $this->table('vote');
        $table->renameColumn('vote_id', 'id');
        $table->renameColumn('vote_title', 'title');
        $table->renameColumn('vote_count', 'count');
        $table->renameColumn('vote_closed', 'closed');
        $table->renameColumn('vote_time', 'time');
    }
}
