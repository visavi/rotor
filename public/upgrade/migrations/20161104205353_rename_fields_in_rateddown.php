<?php

use Phinx\Migration\AbstractMigration;

class RenameFieldsInRateddown extends AbstractMigration
{
    /**
     * Migrate Change.
     */
    public function change()
    {
        $table = $this->table('rateddown');
        $table->renameColumn('rated_id', 'id');
        $table->renameColumn('rated_down', 'down');
        $table->renameColumn('rated_user', 'user');
        $table->renameColumn('rated_time', 'time');
    }
}
