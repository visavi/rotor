<?php

use Phinx\Migration\AbstractMigration;

class AddPhoneToItems extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function change()
    {
        $table = $this->table('items');
        $table->addColumn('phone', 'string', ['limit' => 15, 'after' => 'price', 'null' => true])
            ->update();
    }
}
