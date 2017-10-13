<?php

use Phinx\Migration\AbstractMigration;

class CreateReadblog2Table extends AbstractMigration
{
    /**
     * Change Method.
     */
    public function change()
    {
        if (! $this->hasTable('readblog')) {
            $table = $this->table('readblog', ['collation' => env('DB_COLLATION')]);
            $table->addColumn('blog', 'integer', ['signed' => false])
                ->addColumn('ip', 'string', ['limit' => 15])
                ->addColumn('time', 'integer')
                ->create();
        }
    }
}
