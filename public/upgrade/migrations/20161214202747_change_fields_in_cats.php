<?php

use Phinx\Migration\AbstractMigration;

class ChangeFieldsInCats extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $users = $this->table('cats');
        $users
            ->changeColumn('folder', 'string', ['limit' => 50, 'null' => 'true'])
            ->changeColumn('closed', 'boolean', ['default' => 0])
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
