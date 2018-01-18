<?php

use Phinx\Migration\AbstractMigration;

class DeleteUpdatedAtInPollings extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('pollings');
        $table->removeColumn('updated_at')
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $table = $this->table('pollings');
        $table->addColumn('updated_at', 'integer', ['null' => true])
            ->save();
    }
}
