<?php

use Phinx\Migration\AbstractMigration;

class CreatePollingsTable extends AbstractMigration
{
    /**
     * Change Method.
     */
    public function change()
    {
        if (! $this->hasTable('pollings')) {
            $table = $this->table('pollings', ['collation' => env('DB_COLLATION')]);
            $table->addColumn('relate_type', 'string', ['limit' => 20])
                ->addColumn('relate_id', 'integer')
                ->addColumn('user', 'string', ['limit' => 20])
                ->addColumn('vote', 'boolean', ['default' => true])
                ->addColumn('time', 'integer')
                ->addIndex(['relate_type', 'relate_id', 'user'], ['name' => 'relate_type_idx'])
                ->create();
        }
    }
}
