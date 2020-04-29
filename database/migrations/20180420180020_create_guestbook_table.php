<?php

use Phinx\Migration\AbstractMigration;

class CreateGuestbookTable extends AbstractMigration
{
    /**
     * Change Method.
     */
    public function change()
    {
        if (! $this->hasTable('guestbook')) {
            $table = $this->table('guestbook', ['engine' => config('DB_ENGINE'), 'collation' => config('DB_COLLATION')]);
            $table
                ->addColumn('user_id', 'integer', ['null' => true])
                ->addColumn('text', 'text', ['null' => true])
                ->addColumn('ip', 'varbinary', ['limit' => 16])
                ->addColumn('brow', 'string', ['limit' => 25])
                ->addColumn('created_at', 'integer')
                ->addColumn('reply', 'text', ['null' => true])
                ->addColumn('guest_name', 'string', ['limit' => 20, 'null' => true])
                ->addColumn('edit_user_id', 'integer', ['null' => true])
                ->addColumn('updated_at', 'integer', ['null' => true])
                ->addIndex('created_at')
                ->create();
        }
    }
}
