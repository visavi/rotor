<?php

use Phinx\Migration\AbstractMigration;

class RenameFieldsInRatedoffers extends AbstractMigration
{
    /**
     * Migrate Change.
     */
    public function change()
    {
        $table = $this->table('ratedoffers');
        $table->renameColumn('rated_id', 'id');
        $table->renameColumn('rated_offers', 'offers');
        $table->renameColumn('rated_user', 'user');
        $table->renameColumn('rated_time', 'time');
    }
}
