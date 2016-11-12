<?php

use Phinx\Migration\AbstractMigration;

class RenameFieldsInFilesForum2 extends AbstractMigration
{
    /**
     * Migrate Change.
     */
    public function change()
    {
        $table = $this->table('files_forum');
        $table->renameColumn('topics_id', 'topic_id');
        $table->renameColumn('posts_id', 'post_id');
    }
}
