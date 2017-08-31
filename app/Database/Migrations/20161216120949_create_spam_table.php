<?php

use Phinx\Migration\AbstractMigration;

class CreateSpamTable extends AbstractMigration
{
    /**
     * Change Method.
     */
    public function change()
    {
        if (! $this->hasTable('spam')) {
            $table = $this->table('spam', ['engine' => 'MyISAM', 'collation' => 'utf8mb4_unicode_ci']);
            $table->addColumn('relate', 'boolean')
                ->addColumn('idnum', 'integer', ['signed' => false])
                ->addColumn('user', 'string', ['limit' => 20])
                ->addColumn('login', 'string', ['limit' => 20])
                ->addColumn('text', 'text', ['null' => true])
                ->addColumn('time', 'integer')
                ->addColumn('addtime', 'integer')
                ->addColumn('link', 'string', ['limit' => 100])
                ->addIndex('relate')
                ->addIndex('time')
                ->create();
        }
    }
}
