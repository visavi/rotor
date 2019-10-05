<?php

use Phinx\Migration\AbstractMigration;

class CreateModulesTable extends AbstractMigration
{
    /**
     * Change Method.
     */
    public function change()
    {
        $table = $this->table('modules', ['engine' => config('DB_ENGINE'), 'collation' => config('DB_COLLATION')]);
        $table
            ->addColumn('name', 'string', ['limit' => 50])
            ->addColumn('version', 'string', ['limit' => 10])
            ->addColumn('disabled', 'boolean', ['default' => 0])
            ->addColumn('updated_at', 'integer', ['null' => true])
            ->addColumn('created_at', 'integer')
            ->create();
    }
}
