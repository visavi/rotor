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
            $table->addColumn('user', 'string', ['limit' => 20])
                ->addColumn('login', 'string', ['limit' => 20])
                ->addColumn('text', 'text', ['null' => true])
                ->addColumn('vote', 'boolean', ['default' => false])
                ->addColumn('time', 'integer')
                ->addIndex('user')
                ->create();
        }
    }
}
