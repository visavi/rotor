<?php

use Phinx\Migration\AbstractMigration;

class CreateAdminAdvertsTable extends AbstractMigration
{
    /**
     * Change Method.
     */
    public function change(): void
    {
        if (! $this->hasTable('admin_adverts')) {
            $table = $this->table('admin_adverts', ['engine' => config('DB_ENGINE'), 'collation' => config('DB_COLLATION')]);
            $table
                ->addColumn('site', 'string', ['limit' => 100])
                ->addColumn('name', 'string', ['limit' => 50])
                ->addColumn('color', 'string', ['limit' => 10, 'null' => true])
                ->addColumn('bold', 'boolean', ['default' => 0])
                ->addColumn('user_id', 'integer')
                ->addColumn('created_at', 'integer')
                ->addColumn('deleted_at', 'integer', ['null' => true])
                ->create();
        }
    }
}
