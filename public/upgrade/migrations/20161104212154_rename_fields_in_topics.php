<?php

use Phinx\Migration\AbstractMigration;

class RenameFieldsInTopics extends AbstractMigration
{
    /**
     * Migrate Change.
     */
    public function change()
    {
        $table = $this->table('topics');
        $table->renameColumn('topics_id', 'id');
        $table->renameColumn('topics_forums_id', 'forums_id');
        $table->renameColumn('topics_title', 'title');
        $table->renameColumn('topics_author', 'author');
        $table->renameColumn('topics_closed', 'closed');
        $table->renameColumn('topics_locked', 'locked');
        $table->renameColumn('topics_posts', 'posts');
        $table->renameColumn('topics_last_user', 'last_user');
        $table->renameColumn('topics_last_time', 'last_time');
        $table->renameColumn('topics_mod', 'mod');
        $table->renameColumn('topics_note', 'note');
    }
}
