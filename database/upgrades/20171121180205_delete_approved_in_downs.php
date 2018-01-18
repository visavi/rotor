<?php

use Phinx\Migration\AbstractMigration;

class DeleteApprovedInDowns extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('downs');
        $table->removeColumn('approved')
            ->removeColumn('notice')
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $table = $this->table('downs');
        $table->addColumn('approved', 'boolean', ['default' => false])
            ->addColumn('notice', 'text', ['null' => true])
            ->save();
    }
}
