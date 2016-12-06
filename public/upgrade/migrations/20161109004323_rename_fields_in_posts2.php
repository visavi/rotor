<?php

use Phinx\Migration\AbstractMigration;

class RenameFieldsInPosts2 extends AbstractMigration
{
    /**
     * Migrate Change.
     */
    public function change()
    {
        $table = $this->table('posts');
        $table->renameColumn('forums_id', 'forum_id');
        $table->renameColumn('topics_id', 'topic_id');
    }
}
