<?php

use Phinx\Migration\AbstractMigration;

class RenameFieldsInPosts extends AbstractMigration
{
    /**
     * Migrate Change.
     */
    public function change()
    {
        $table = $this->table('posts');
        $table->renameColumn('posts_id', 'id');
        $table->renameColumn('posts_forums_id', 'forums_id');
        $table->renameColumn('posts_topics_id', 'topics_id');
        $table->renameColumn('posts_user', 'user');
        $table->renameColumn('posts_text', 'text');
        $table->renameColumn('posts_time', 'time');
        $table->renameColumn('posts_ip', 'ip');
        $table->renameColumn('posts_brow', 'brow');
        $table->renameColumn('posts_edit', 'edit');
        $table->renameColumn('posts_edit_time', 'edit_time');
    }
}
