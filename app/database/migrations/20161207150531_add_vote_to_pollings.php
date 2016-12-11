<?php

use Phinx\Migration\AbstractMigration;

class AddVoteToPollings extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function change()
    {
        $table = $this->table('pollings');
        $table->addColumn('vote', 'boolean', [
            'after' => 'user',
            'default' => true
        ])
            ->update();
    }
}
