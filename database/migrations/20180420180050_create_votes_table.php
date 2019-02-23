<?php

use Phinx\Migration\AbstractMigration;

class CreateVotesTable extends AbstractMigration
{
    /**
     * Change Method.
     */
    public function change()
    {
        if (! $this->hasTable('votes')) {
            $table = $this->table('votes', ['engine' => env('DB_ENGINE'), 'collation' => env('DB_COLLATION')]);
            $table
                ->addColumn('title', 'string', ['limit' => 100])
                ->addColumn('count', 'integer', ['default' => 0])
                ->addColumn('closed', 'boolean', ['default' => 0])
                ->addColumn('created_at', 'integer')
                ->addColumn('topic_id', 'integer', ['null' => true])
                ->create();
        }
    }
}
