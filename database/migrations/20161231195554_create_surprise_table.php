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
            $table->addColumn('user', 'string', ['limit' => 20])
                ->addColumn('year', 'year', ['limit' => 4])
                ->addColumn('time', 'integer')
                ->addIndex('user')
                ->create();
        }
    }
}
