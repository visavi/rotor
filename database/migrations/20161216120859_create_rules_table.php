<?php

use Phinx\Migration\AbstractMigration;

class CreateRulesTable extends AbstractMigration
{
    /**
     * Change Method.
     */
    public function change()
    {
        if (! $this->hasTable('rules')) {
            $table = $this->table('rules', ['collation' => env('DB_COLLATION')]);
            $table
                ->addColumn('text', 'text', ['null' => true])
                ->addColumn('created_at', 'integer')
                ->create();
        }
    }
}
