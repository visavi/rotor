<?php

use Phinx\Migration\AbstractMigration;

class CreateRatingTable extends AbstractMigration
{
    /**
     * Change Method.
     */
    public function change()
    {
        $table = $this->table('rating', ['engine' => 'MyISAM',  'collation' => 'utf8mb4_unicode_ci']);
        $table->addColumn('user', 'string', ['limit' => 20])
            ->addColumn('login', 'string', ['limit' => 20])
            ->addColumn('text', 'text', ['null' => true])
            ->addColumn('vote', 'boolean', ['default' => 0])
            ->addColumn('time', 'integer')
            ->addIndex('user')
            ->create();

    }
}
