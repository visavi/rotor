<?php

use Phinx\Migration\AbstractMigration;

class RenameFieldsInForums extends AbstractMigration
{
    /**
     * Migrate Change.
     */
    public function change()
    {
        $table = $this->table('forums');
        $table->renameColumn('forums_id', 'id');
        $table->renameColumn('forums_order', 'order');
        $table->renameColumn('forums_parent', 'parent');
        $table->renameColumn('forums_title', 'title');
        $table->renameColumn('forums_desc', 'desc');
        $table->renameColumn('forums_topics', 'topics');
        $table->renameColumn('forums_posts', 'posts');
        $table->renameColumn('forums_last_id', 'last_id');
        $table->renameColumn('forums_last_themes', 'last_themes');
        $table->renameColumn('forums_last_user', 'last_user');
        $table->renameColumn('forums_last_time', 'last_time');
        $table->renameColumn('forums_closed', 'closed');
    }
}
