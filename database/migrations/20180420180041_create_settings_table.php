<?php

use Phinx\Migration\AbstractMigration;

class CreateSettingsTable extends AbstractMigration
{
    /**
     * Change Method.
     */
    public function change()
    {
        if (! $this->hasTable('settings')) {

            $table = $this->table('settings', [
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
