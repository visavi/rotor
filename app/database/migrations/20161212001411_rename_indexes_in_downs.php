<?php

use Phinx\Migration\AbstractMigration;

class RenameIndexesInDowns extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('downs');
        $table->removeIndexByName('downs_cats_id');
        $table->removeIndexByName('downs_time');
        $table->removeIndexByName('downs_text');
        $table->removeIndexByName('downs_title');
        $table->addIndex('category_id')
            ->addIndex('time')
            ->addIndex('text', ['type' => 'fulltext'])
            ->addIndex('title', ['type' => 'fulltext'])
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $table = $this->table('downs');
        $table->removeIndexByName('category_id');
        $table->removeIndexByName('time');
        $table->removeIndexByName('text');
        $table->removeIndexByName('title');
        $table->addIndex('category_id', ['name' => 'downs_cats_id'])
            ->addIndex('time', ['name' => 'downs_time'])
            ->addIndex('text', ['type' => 'fulltext', 'name' => 'downs_text'])
            ->addIndex('title', ['type' => 'fulltext', 'name' => 'downs_title'])
            ->save();
    }
}
