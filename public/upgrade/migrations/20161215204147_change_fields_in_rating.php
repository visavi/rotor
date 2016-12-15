<?php

use Phinx\Migration\AbstractMigration;

class ChangeFieldsInRating extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $users = $this->table('rating');
        $users
            ->changeColumn('text', 'text', ['null' => true])
            ->changeColumn('time', 'integer')
            ->changeColumn('vote', 'boolean', ['default' => 0])
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
