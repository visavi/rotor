<?php

use Phinx\Migration\AbstractMigration;

class RenameFieldsInvoteanswer extends AbstractMigration
{
    /**
     * Migrate Change.
     */
    public function change()
    {
        $table = $this->table('voteanswer');
        $table->renameColumn('answer_id', 'id');
        $table->renameColumn('answer_vote_id', 'vote_id');
        $table->renameColumn('answer_option', 'option');
        $table->renameColumn('answer_result', 'result');
    }
}
