<?php

use Phinx\Migration\AbstractMigration;

class EditFieldsInFiles extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('files');
        $table
            ->changeColumn('relate_id', 'integer')
            ->changeColumn('size', 'integer')
            ->save();

        $table
            ->removeIndex('user_id')
            ->removeIndex('created_at')
            ->addIndex('user_id')
            ->addIndex('created_at')
            ->save();

    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
