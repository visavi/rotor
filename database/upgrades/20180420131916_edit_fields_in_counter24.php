<?php

use Phinx\Migration\AbstractMigration;

class EditFieldsInCounter24 extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('counter24');
        $table
            ->changeColumn('hour', 'integer')
            ->changeColumn('hosts', 'integer')
            ->changeColumn('hits', 'integer')
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
