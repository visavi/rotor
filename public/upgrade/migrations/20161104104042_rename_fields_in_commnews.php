<?php

use Phinx\Migration\AbstractMigration;

class RenameFieldsInCommnews extends AbstractMigration
{
    /**
     * Migrate Change.
     */
    public function change()
    {
        $table = $this->table('commnews');
        $table->renameColumn('commnews_id', 'id');
        $table->renameColumn('commnews_news_id', 'news_id');
        $table->renameColumn('commnews_text', 'text');
        $table->renameColumn('commnews_author', 'author');
        $table->renameColumn('commnews_time', 'time');
        $table->renameColumn('commnews_ip', 'ip');
        $table->renameColumn('commnews_brow', 'brow');
    }
}
