<?php

use Phinx\Migration\AbstractMigration;

class EditFieldsInComments extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('comments');
        $table
            ->changeColumn('relate_id', 'integer')
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
