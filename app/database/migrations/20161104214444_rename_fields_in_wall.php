<?php

use Phinx\Migration\AbstractMigration;

class RenameFieldsInWall extends AbstractMigration
{
    /**
     * Migrate Change.
     */
    public function change()
    {
        $table = $this->table('wall');
        $table->renameColumn('wall_id', 'id');
        $table->renameColumn('wall_user', 'user');
        $table->renameColumn('wall_login', 'login');
        $table->renameColumn('wall_text', 'text');
        $table->renameColumn('wall_time', 'time');
    }
}
