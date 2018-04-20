<?php

use Phinx\Migration\AbstractMigration;

class EditFieldsInVoteanswer extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('voteanswer');
        $table
            ->changeColumn('vote_id', 'integer')
            ->changeColumn('result', 'integer', ['default' => 0])
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
