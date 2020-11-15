<?php

use App\Models\Message;
use Phinx\Migration\AbstractMigration;

class CreateMessagesTable extends AbstractMigration
{
    /**
     * Change Method.
     */
    public function change()
    {
        $table = $this->table('messages', ['engine' => config('DB_ENGINE'), 'collation' => config('DB_COLLATION')]);
        $table
            ->addColumn('user_id', 'integer')
            ->addColumn('author_id', 'integer')
            ->addColumn('text', 'text', ['null' => true])
            //->addColumn('type', 'enum', ['values' => [Message::IN, Message::OUT]])
            ->addColumn('type', 'string', ['limit' => 3])
            ->addColumn('reading', 'boolean', ['default' => 0])
            ->addColumn('created_at', 'integer')
            ->addIndex(['user_id', 'author_id'], ['name' => 'user_id'])
            ->addIndex('created_at')
            ->create();
    }
}
