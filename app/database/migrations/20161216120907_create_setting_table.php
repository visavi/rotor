<?php

use Phinx\Migration\AbstractMigration;

class CreateSettingTable extends AbstractMigration
{
    /**
     * Change Method.
     */
    public function change()
    {
        $table = $this->table('setting', [
            'id' => false,
            'primary_key' => 'name',
            'collation' => 'utf8mb4_unicode_ci'
        ]);
        $table->addColumn('name', 'string', ['limit' => 25])
            ->addColumn('value', 'string')
            ->create();
    }
}
