<?php

use Phinx\Migration\AbstractMigration;

class CreateReadsTable extends AbstractMigration
{
    /**
     * Change Method.
     */
    public function change()
    {
        if (! $this->hasTable('reads')) {
            $table = $this->table('reads', ['collation' => env('DB_COLLATION')]);
            $table->addColumn('relate_type', 'string', ['limit' => 20])
                ->addColumn('relate_id', 'integer')
                ->addColumn('ip', 'string', ['limit' => 15])
                ->addColumn('created_at', 'integer')
                ->addIndex(['relate_type', 'relate_id', 'ip'], ['name' => 'relate_type'])
                ->create();
        }
    }
}
