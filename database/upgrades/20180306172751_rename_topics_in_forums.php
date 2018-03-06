<?php

use Phinx\Migration\AbstractMigration;

class RenameTopicsInForums extends AbstractMigration
{
    /**
     * Migrate Change.
     */
    public function change()
    {
        $table = $this->table('forums');
        $table->renameColumn('topics', 'count_topics');
        $table->renameColumn('posts', 'count_posts');
    }
}
