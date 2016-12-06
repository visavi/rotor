<?php

use Phinx\Migration\AbstractMigration;

class RenameFieldsInNews extends AbstractMigration
{
    /**
     * Migrate Change.
     */
    public function change()
    {
        $table = $this->table('news');
        $table->renameColumn('news_id', 'id');
        $table->renameColumn('news_title', 'title');
        $table->renameColumn('news_text', 'text');
        $table->renameColumn('news_author', 'author');
        $table->renameColumn('news_image', 'image');
        $table->renameColumn('news_time', 'time');
        $table->renameColumn('news_comments', 'comments');
        $table->renameColumn('news_closed', 'closed');
        $table->renameColumn('news_top', 'top');
    }
}
