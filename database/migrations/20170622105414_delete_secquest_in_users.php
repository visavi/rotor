<?php

use Phinx\Migration\AbstractMigration;

class DeleteSecquestInUsers extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('users');
        $table
            ->removeColumn('secquest')
            ->removeColumn('secanswer')
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $table = $this->table('users');
        $table
            ->addColumn('secquest', 'string', ['limit' => 50, 'null' => true])
            ->addColumn('secanswer', 'string', ['limit' => 40, 'null' => true])
            ->save();
    }
}
