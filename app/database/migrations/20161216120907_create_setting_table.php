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
                'id' => false,
                'primary_key' => 'name',
                'engine' => 'MyISAM',
                'collation' => 'utf8mb4_unicode_ci'
            ]);
            $table->addColumn('name', 'string', ['limit' => 25])
                ->addColumn('value', 'string')
                ->create();
        }
    }
}
