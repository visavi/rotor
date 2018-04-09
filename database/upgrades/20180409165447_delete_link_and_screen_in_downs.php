<?php

use Phinx\Migration\AbstractMigration;

class DeleteLinkAndScreenInDowns extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('downs');
        $table->removeColumn('link')
            ->removeColumn('screen')
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $table = $this->table('loads');
        $table->addColumn('link', 'string', ['limit' => 50])
            ->addColumn('screen', 'string', ['limit' => 50, 'null' => true])
            ->save();
    }
}
