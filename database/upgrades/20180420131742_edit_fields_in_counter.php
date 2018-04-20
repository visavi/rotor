<?php

use Phinx\Migration\AbstractMigration;

class EditFieldsInCounter extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('counter');
        $table
            ->changeColumn('hours', 'integer')
            ->changeColumn('days', 'integer')
            ->changeColumn('allhosts', 'integer')
            ->changeColumn('allhits', 'integer')
            ->changeColumn('dayhosts', 'integer')
            ->changeColumn('dayhits', 'integer')
            ->changeColumn('hosts24', 'integer')
            ->changeColumn('hits24', 'integer')
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
