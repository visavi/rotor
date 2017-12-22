<?php

use Phinx\Migration\AbstractMigration;

class CreateFloodTable extends AbstractMigration
{
    /**
     * Change Method.
     */
    public function change()
    {
        if (! $this->hasTable('flood')) {
            $table = $this->table('flood', ['collation' => env('DB_COLLATION')]);
            $table->addColumn('user', 'string', ['limit' => 20])
                ->addColumn('page', 'string', ['limit' => 30])
                ->addColumn('time', 'integer')
                ->addIndex('user')
                ->create();
        }
    }
}
