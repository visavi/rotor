<?php

use Phinx\Migration\AbstractMigration;

class RenameIndexesInSpam extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('spam');
        $table->removeIndexByName('spam_key');
        $table->removeIndexByName('spam_time');
        $table->addIndex('relate')
            ->addIndex('time')
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $table = $this->table('spam');
        $table->removeIndexByName('relate');
        $table->removeIndexByName('time');
        $table->addIndex('relate', ['name' => 'spam_key'])
            ->addIndex('time', ['name' => 'spam_time'])
            ->save();
    }
}
