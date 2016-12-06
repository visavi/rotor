<?php

use Phinx\Migration\AbstractMigration;

class RenameFieldsInVotepoll extends AbstractMigration
{
    /**
     * Migrate Change.
     */
    public function change()
    {
        $table = $this->table('votepoll');
        $table->renameColumn('poll_id', 'id');
        $table->renameColumn('poll_vote_id', 'vote_id');
        $table->renameColumn('poll_user', 'user');
        $table->renameColumn('poll_time', 'time');
    }
}
