<?php

use Phinx\Migration\AbstractMigration;

class RenameFieldsInCommphoto extends AbstractMigration
{
    /**
     * Migrate Change.
     */
    public function change()
    {
        $table = $this->table('commphoto');
        $table->renameColumn('commphoto_id', 'id');
        $table->renameColumn('commphoto_gid', 'gid');
        $table->renameColumn('commphoto_text', 'text');
        $table->renameColumn('commphoto_user', 'user');
        $table->renameColumn('commphoto_time', 'time');
        $table->renameColumn('commphoto_ip', 'ip');
        $table->renameColumn('commphoto_brow', 'brow');
    }
}
