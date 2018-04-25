<?php

use Phinx\Migration\AbstractMigration;

class CreateChatsTable extends AbstractMigration
{
    /**
     * Change Method.
     */
    public function change()
    {
        if (! $this->hasTable('chats')) {
            $table = $this->table('chats', ['collation' => env('DB_COLLATION')]);
            $table
                ->addColumn('user_id', 'integer')
                ->addColumn('text', 'text', ['null' => true])
                ->addColumn('ip', 'string', ['limit' => 15])
                ->addColumn('brow', 'string', ['limit' => 25])
                ->addColumn('created_at', 'integer', ['null' => true])
                ->addColumn('edit_user_id', 'integer', ['null' => true])
                ->addColumn('updated_at', 'integer', ['null' => true])
                ->addIndex('created_at')
                ->create();
        }
    }
}
