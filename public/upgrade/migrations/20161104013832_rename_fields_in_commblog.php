<?php

use Phinx\Migration\AbstractMigration;

class RenameFieldsInCommblog extends AbstractMigration
{
    /**
     * Migrate Change.
     */
    public function change()
    {
        $table = $this->table('commblog');
        $table->renameColumn('commblog_id', 'id');
        $table->renameColumn('commblog_cats', 'cats');
        $table->renameColumn('commblog_blog', 'blog');
        $table->renameColumn('commblog_text', 'text');
        $table->renameColumn('commblog_author', 'author');
        $table->renameColumn('commblog_time', 'time');
        $table->renameColumn('commblog_ip', 'ip');
        $table->renameColumn('commblog_brow', 'brow');
    }
}
