<?php

use Phinx\Migration\AbstractMigration;

class EditFieldsInVote extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('vote');
        $table
            ->changeColumn('count', 'integer', ['default' => 0])
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
