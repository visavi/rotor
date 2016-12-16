<?php

use Phinx\Migration\AbstractMigration;

class CreatePollingsTable extends AbstractMigration
{
    /**
     * Change Method.
     */
    public function change()
    {
        $table = $this->table('pollings', ['collation' => 'utf8mb4_unicode_ci']);
        $table->addColumn('relate_type', 'string', ['limit' => 20])
            ->addColumn('relate_id', 'integer')
            ->addColumn('user', 'string', ['limit' => 20])
            ->addColumn('vote', 'boolean', ['default' => 1])
            ->addColumn('time', 'integer')
            ->addIndex(['relate_type', 'relate_id', 'user'], ['name' => 'relate_type'])
            ->create();
    }
}
