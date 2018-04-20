<?php

use Phinx\Migration\AbstractMigration;

class EditFieldsInCounter31 extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('counter31');
        $table
            ->changeColumn('days', 'integer')
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
