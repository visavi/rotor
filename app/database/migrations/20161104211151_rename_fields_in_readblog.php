<?php

use Phinx\Migration\AbstractMigration;

class RenameFieldsInReadblog extends AbstractMigration
{
    /**
     * Migrate Change.
     */
    public function change()
    {
        $table = $this->table('readblog');
        $table->renameColumn('read_id', 'id');
        $table->renameColumn('read_blog', 'blog');
        $table->renameColumn('read_ip', 'ip');
        $table->renameColumn('read_time', 'time');
    }
}
