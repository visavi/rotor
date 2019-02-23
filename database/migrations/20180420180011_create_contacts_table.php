<?php

use Phinx\Migration\AbstractMigration;

class CreateContactsTable extends AbstractMigration
{
    /**
     * Change Method.
     */
    public function change()
    {
        if (! $this->hasTable('contacts')) {
            $table = $this->table('contacts', ['engine' => env('DB_ENGINE'), 'collation' => env('DB_COLLATION')]);
            $table
                ->addColumn('user_id', 'integer')
                ->addColumn('contact_id', 'integer')
                ->addColumn('text', 'text', ['null' => true])
                ->addColumn('created_at', 'integer')
                ->addIndex('user_id')
                ->addIndex('created_at')
                ->create();
        }
    }
}
