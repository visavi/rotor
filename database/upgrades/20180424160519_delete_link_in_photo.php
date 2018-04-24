<?php

use Phinx\Migration\AbstractMigration;

class DeleteLinkInPhoto extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('photo');
        $table->removeColumn('link')
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $table = $this->table('photo');
        $table->addColumn('link', 'string', ['limit' => 30])
            ->save();
    }
}
