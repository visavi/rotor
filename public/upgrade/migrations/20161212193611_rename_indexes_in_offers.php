<?php

use Phinx\Migration\AbstractMigration;

class RenameIndexesInOffers extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('offers');
        $table->removeIndexByName('offers_time');
        $table->removeIndexByName('offers_votes');
        $table->addIndex('time')
            ->addIndex('votes')
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $table = $this->table('offers');
        $table->removeIndexByName('time');
        $table->removeIndexByName('votes');
        $table->addIndex('time', ['name' => 'offers_time'])
            ->addIndex('votes', ['name' => 'offers_votes'])
            ->save();
    }
}
