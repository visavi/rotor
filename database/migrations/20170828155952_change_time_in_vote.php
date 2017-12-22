<?php

use Phinx\Migration\AbstractMigration;

class ChangeTimeInVote extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('vote');

        $table->renameColumn('time', 'created_at')
            ->changeColumn('topic_id', 'integer', ['null' => true])
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $table = $this->table('vote');

        $table->renameColumn('created_at', 'time')
            ->changeColumn('topic_id', 'integer', ['default' => 0])
            ->save();
    }
}
