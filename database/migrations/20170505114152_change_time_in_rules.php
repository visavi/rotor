<?php

use Phinx\Migration\AbstractMigration;

class ChangeTimeInRules extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('rules');

        $table->renameColumn('time', 'created_at')
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $table = $this->table('rules');

        $table->renameColumn('created_at', 'time')
            ->save();
    }
}
