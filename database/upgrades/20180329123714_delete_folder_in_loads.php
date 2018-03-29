<?php

use Phinx\Migration\AbstractMigration;

class DeleteFolderInLoads extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('loads');
        $table->removeColumn('folder')
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $table = $this->table('loads');
        $table->addColumn('folder', 'string', ['limit' => 50, 'null' => true])
            ->save();
    }
}
