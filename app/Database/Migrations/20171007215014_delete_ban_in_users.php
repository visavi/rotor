<?php

use Phinx\Migration\AbstractMigration;

class DeleteBanInUsers extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('users');
        $table
            ->removeColumn('timeban')
            ->removeColumn('timelastban')
            ->removeColumn('reasonban')
            ->removeColumn('loginsendban')
            ->removeColumn('explainban')
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $table = $this->table('users');
        $table
            ->addColumn('timeban', 'integer', ['default' => 0])
            ->addColumn('timelastban', 'integer', ['default' => 0])
            ->addColumn('reasonban', 'text', ['null' => true])
            ->addColumn('loginsendban', 'string', ['limit' => 20, 'null' => true])
            ->addColumn('explainban', 'boolean', ['signed' => false, 'default' => false])
            ->save();
    }
}
