<?php

use Phinx\Migration\AbstractMigration;

class RenameTopicInBookmarks extends AbstractMigration
{
    /**
     * Migrate Change.
     */
    public function change()
    {
        $table = $this->table('bookmarks');
        $table->renameColumn('forum', 'forum_id');
        $table->renameColumn('topic', 'topic_id');
    }
}
