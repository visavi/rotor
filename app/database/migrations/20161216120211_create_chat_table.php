<?php

use Phinx\Migration\AbstractMigration;

class CreateChatTable extends AbstractMigration
{
    /**
     * Change Method.
     */
    public function change()
    {
        $table = $this->table('chat', ['collation' => 'utf8mb4_unicode_ci']);
        $table->addColumn('user', 'string', ['limit' => 20])
            ->addColumn('text', 'text', ['null' => true])
            ->addColumn('ip', 'string', ['limit' => 15])
            ->addColumn('brow', 'string', ['limit' => 25])
            ->addColumn('time', 'integer')
            ->addColumn('edit', 'string', ['limit' => 20, 'null' => true])
            ->addColumn('edit_time', 'integer', ['default' => 0])
            ->addIndex('time')
            ->create();
    }
}
