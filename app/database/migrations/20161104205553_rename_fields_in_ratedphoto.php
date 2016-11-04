<?php

use Phinx\Migration\AbstractMigration;

class RenameFieldsInRatedphoto extends AbstractMigration
{
    /**
     * Migrate Change.
     */
    public function change()
    {
        $table = $this->table('ratedphoto');
        $table->renameColumn('rated_id', 'id');
        $table->renameColumn('rated_photo', 'photo');
        $table->renameColumn('rated_user', 'user');
        $table->renameColumn('rated_time', 'time');
    }
}
