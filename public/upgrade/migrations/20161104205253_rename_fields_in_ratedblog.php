<?php

use Phinx\Migration\AbstractMigration;

class RenameFieldsInRatedblog extends AbstractMigration
{
    /**
     * Migrate Change.
     */
    public function change()
    {
        $table = $this->table('ratedblog');
        $table->renameColumn('rated_id', 'id');
        $table->renameColumn('rated_blog', 'blog');
        $table->renameColumn('rated_user', 'user');
        $table->renameColumn('rated_time', 'time');
    }
}
