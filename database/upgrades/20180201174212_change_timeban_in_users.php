<?php

use Phinx\Migration\AbstractMigration;

class ChangeTimebanInUsers extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('users');
        $table->changeColumn('timeban', 'integer', ['null' => true])
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $table = $this->table('users');
        $table
            ->changeColumn('timeban', 'integer', ['default' => 0])
            ->save();
    }
}
