<?php

use Phinx\Migration\AbstractMigration;

class EditFieldsInTransfers extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('transfers');
        $table
            ->changeColumn('total', 'integer', ['default' => 0])
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
