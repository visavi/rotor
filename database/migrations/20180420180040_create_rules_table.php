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
            $table = $this->table('rules', ['engine' => config('DB_ENGINE'), 'collation' => config('DB_COLLATION')]);
            $table
                ->addColumn('text', 'text', ['null' => true])
                ->addColumn('created_at', 'integer')
                ->create();
        }
    }
}
