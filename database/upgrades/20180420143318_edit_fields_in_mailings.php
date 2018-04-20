<?php

use Phinx\Migration\AbstractMigration;

class EditFieldsInMailings extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('mailings');
        $table
            ->changeColumn('created_at', 'integer')
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
