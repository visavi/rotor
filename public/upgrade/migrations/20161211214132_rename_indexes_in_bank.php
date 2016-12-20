<?php

use Phinx\Migration\AbstractMigration;

class RenameIndexesInBank extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('bank');
        if ($table->exists()) {
            $table = $this->table('bank');
            $table->removeIndexByName('bank_user');
            $table->addIndex('user', ['unique' => true])
                ->save();
        }
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $table = $this->table('bank');
        if ($table->exists()) {
            $table = $this->table('bank');
            $table->removeIndexByName('user');
            $table->addIndex('user', ['unique' => true, 'name' => 'bank_user'])
              ->save();
        }
    }
}
