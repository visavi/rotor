<?php

use Phinx\Migration\AbstractMigration;

class CreateRatingTable extends AbstractMigration
{
    /**
     * Change Method.
     */
    public function change()
    {
        if (! $this->hasTable('rating')) {
            $table = $this->table('rating', ['engine' => config('DB_ENGINE'), 'collation' => config('DB_COLLATION')]);
            $table
                ->addColumn('user_id', 'integer')
                ->addColumn('recipient_id', 'integer')
                ->addColumn('text', 'text', ['null' => true])
                ->addColumn('vote', 'string', ['limit' => 1])
                ->addColumn('created_at', 'integer')
                ->addIndex('user_id')
                ->create();
        }
    }
}
