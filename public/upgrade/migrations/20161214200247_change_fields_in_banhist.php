<?php

use Phinx\Migration\AbstractMigration;

class ChangeFieldsInBanhist extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $users = $this->table('banhist');
        $users->changeColumn('reason', 'text', ['null' => true])
            ->changeColumn('type', 'boolean', ['default' => 0])
            ->changeColumn('term', 'integer')
            ->changeColumn('time', 'integer')
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
