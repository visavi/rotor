<?php

use Phinx\Migration\AbstractMigration;

class EditFieldsInStatus extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('status');
        $table
            ->changeColumn('topoint', 'integer')
            ->changeColumn('point', 'integer')
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
