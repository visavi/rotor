<?php

use Phinx\Migration\AbstractMigration;

class DeleteCreditInUsers extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('users');
        $table
            ->removeColumn('timecredit')
            ->removeColumn('sumcredit')
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $table = $this->table('users');
        $table
            ->addColumn('timecredit', 'integer', ['default' => 0])
            ->addColumn('sumcredit', 'integer', ['default' => 0])
            ->save();
    }
}
