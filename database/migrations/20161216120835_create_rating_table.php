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
            $table = $this->table('rating', ['collation' => env('DB_COLLATION')]);
            $table
                ->addColumn('user_id', 'integer')
                ->addColumn('recipient_id', 'integer')
                ->addColumn('text', 'text', ['null' => true])
                ->addColumn('vote', 'boolean', ['default' => 0])
                ->addColumn('created_at', 'integer')
                ->addIndex('user_id')
                ->create();
        }
    }
}
