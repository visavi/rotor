<?php

use Phinx\Migration\AbstractMigration;

class RenameIndexesInBlacklist extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('blacklist');
        $table->removeIndexByName('black_type');
        $table->removeIndexByName('black_value');
        $table->addIndex('type')
            ->addIndex('value')
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $table = $this->table('blacklist');
        $table->removeIndexByName('type');
        $table->removeIndexByName('value');
        $table->addIndex('type', ['name' => 'black_type'])
            ->addIndex('value', ['name' => 'black_value'])
            ->save();
    }
}
