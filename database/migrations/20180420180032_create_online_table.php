<?php

use Phinx\Migration\AbstractMigration;

class CreateOnlineTable extends AbstractMigration
{
    /**
     * Change Method.
     */
    public function change()
    {
        if (! $this->hasTable('online')) {
            $table = $this->table('online', [
                'id'          => false,
                'primary_key' => 'uid',
                'engine'      => config('DB_ENGINE'),
                'collation'   => config('DB_COLLATION'),
            ]);

            $table
                ->addColumn('uid', 'string', ['limit' => 32])
                ->addColumn('ip', 'varbinary', ['limit' => 16])
                ->addColumn('brow', 'string', ['limit' => 25])
                ->addColumn('user_id', 'integer', ['null' => true])
                ->addColumn('updated_at', 'integer', ['null' => true])
                ->create();
        }
    }
}
