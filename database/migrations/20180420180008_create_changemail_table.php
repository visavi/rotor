<?php

use Phinx\Migration\AbstractMigration;

class CreateChangemailTable extends AbstractMigration
{
    /**
     * Change Method.
     */
    public function change()
    {
        if (! $this->hasTable('changemail')) {
            $table = $this->table('changemail', ['engine' => env('DB_ENGINE'), 'collation' => env('DB_COLLATION')]);
            $table
                ->addColumn('user_id', 'integer')
                ->addColumn('mail', 'string', ['limit' => 50])
                ->addColumn('hash', 'string', ['limit' => 25])
                ->addColumn('created_at', 'integer')
                ->create();
        }
    }
}
