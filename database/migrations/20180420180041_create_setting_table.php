<?php

use Phinx\Migration\AbstractMigration;

class CreateSettingTable extends AbstractMigration
{
    /**
     * Change Method.
     */
    public function change()
    {
        if (! $this->hasTable('setting')) {

            $table = $this->table('setting', [
                'id'          => false,
                'primary_key' => 'name',
                'collation'   => env('DB_COLLATION')
            ]);
            $table
                ->addColumn('name', 'string', ['limit' => 25])
                ->addColumn('value', 'string')
                ->create();
        }
    }
}
