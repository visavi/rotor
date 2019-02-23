<?php

use Phinx\Migration\AbstractMigration;

class CreateModulesTable extends AbstractMigration
{
    /**
     * Change Method.
     */
    public function change()
    {
        $table = $this->table('modules', ['engine' => env('DB_ENGINE'), 'collation' => env('DB_COLLATION')]);
        $table
            ->addColumn('name', 'string', ['limit' => 50])
            ->addColumn('version', 'string', ['limit' => 10])
            ->addColumn('updated_at', 'integer', ['null' => true])
            ->addColumn('created_at', 'integer')
            ->create();
    }
}
