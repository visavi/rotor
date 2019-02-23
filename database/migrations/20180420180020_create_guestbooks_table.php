<?php

use Phinx\Migration\AbstractMigration;

class CreateGuestbooksTable extends AbstractMigration
{
    /**
     * Change Method.
     */
    public function change()
    {
        if (! $this->hasTable('guestbooks')) {
            $table = $this->table('guestbooks', ['engine' => env('DB_ENGINE'), 'collation' => env('DB_COLLATION')]);
            $table
                ->addColumn('user_id', 'integer')
                ->addColumn('text', 'text', ['null' => true])
                ->addColumn('ip', 'string', ['limit' => 15])
                ->addColumn('brow', 'string', ['limit' => 25])
                ->addColumn('created_at', 'integer')
                ->addColumn('reply', 'text', ['null' => true])
                ->addColumn('edit_user_id', 'integer', ['null' => true])
                ->addColumn('updated_at', 'integer', ['null' => true])
                ->addIndex('created_at')
                ->create();
        }
    }
}
