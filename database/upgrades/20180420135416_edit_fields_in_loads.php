<?php

use Phinx\Migration\AbstractMigration;

class EditFieldsInLoads extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('loads');
        $table
            ->changeColumn('sort', 'integer', ['default' => 0])
            ->changeColumn('parent_id', 'integer', ['default' => 0])
            ->changeColumn('count_downs', 'integer', ['default' => 0])
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
