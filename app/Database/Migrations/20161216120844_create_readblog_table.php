<?php

use Phinx\Migration\AbstractMigration;

class CreateReadblogTable extends AbstractMigration
{
    /**
     * Change Method.
     */
    public function change()
    {
        if (! $this->hasTable('readblog')) {
            $table = $this->table('readblog', ['engine' => 'MyISAM', 'collation' => 'utf8mb4_unicode_ci']);
            $table->addColumn('blog', 'integer', ['signed' => false])
                ->addColumn('ip', 'string', ['limit' => 15])
                ->addColumn('time', 'integer')
                ->create();
        }
    }
}
