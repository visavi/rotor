<?php

use Phinx\Migration\AbstractMigration;

class CreateChangemailTable extends AbstractMigration
{
    /**
     * Change Method.
     */
    public function change()
    {
        if (! $this->hasTable('changemail')) {
            $table = $this->table('changemail', ['collation' => env('DB_COLLATION')]);
            $table->addColumn('user', 'string', ['limit' => 20])
                ->addColumn('mail', 'string', ['limit' => 50])
                ->addColumn('hash', 'string', ['limit' => 25])
                ->addColumn('time', 'integer')
                ->create();
        }
    }
}
