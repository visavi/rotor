<?php

use Phinx\Migration\AbstractMigration;

class CreateSmilesTable extends AbstractMigration
{
    /**
     * Change Method.
     */
    public function change()
    {
        if (! $this->hasTable('smiles')) {
            $table = $this->table('smiles', ['collation' => env('DB_COLLATION')]);
            $table
                ->addColumn('name', 'string', ['limit' => 100])
                ->addColumn('code', 'string', ['limit' => 20])
                ->addIndex('code')
                ->create();
        }
    }
}
