<?php

use Phinx\Migration\AbstractMigration;

class RenameIndexesInNews extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('news');
        $table->removeIndexByName('news_time');
        $table->addIndex('time')
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $table = $this->table('news');
        $table->removeIndexByName('time');
        $table->addIndex('time', ['name' => 'news_time'])
            ->save();
    }
}
