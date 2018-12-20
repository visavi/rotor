<?php

use Phinx\Migration\AbstractMigration;

class CreateStickersTable extends AbstractMigration
{
    /**
     * Change Method.
     */
    public function change(): void
    {
        if (! $this->hasTable('stickers')) {
            $table = $this->table('stickers', ['collation' => env('DB_COLLATION')]);
            $table
                ->addColumn('category_id', 'integer')
                ->addColumn('name', 'string', ['limit' => 100])
                ->addColumn('code', 'string', ['limit' => 20])
                ->addIndex('code')
                ->create();
        }
    }
}
