<?php

use Phinx\Migration\AbstractMigration;

class CreateSurpriseTable extends AbstractMigration
{
    /**
     * Change Method.
     */
    public function change()
    {
        if (! $this->hasTable('surprise')) {
            $table = $this->table('surprise', ['collation' => env('DB_COLLATION')]);
            $table
                ->addColumn('user_id', 'integer')
                ->addColumn('year', 'year', ['limit' => 4])
                ->addColumn('created_at', 'integer')
                ->addIndex('user_id')
                ->create();
        }
    }
}
