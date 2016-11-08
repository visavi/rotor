<?php

use Phinx\Migration\AbstractMigration;

class RenameFieldsInTopics2 extends AbstractMigration
{
    /**
     * Migrate Change.
     */
    public function change()
    {
        $table = $this->table('topics');
        $table->renameColumn('forums_id', 'forum_id');
        $table->renameColumn('mod', 'moderators');
    }
}
