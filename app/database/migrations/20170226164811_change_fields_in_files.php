<?php

use Phinx\Migration\AbstractMigration;

class ChangeFieldsInFiles extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('files');

        $table->renameColumn('post_id', 'relate_id')
            ->removeColumn('topic_id')
            ->addColumn('relate_type', 'string', [
                'after' => 'id',
                'limit' => 20,
            ])->save();

        $table
            ->removeIndexByName('post_id')
            ->addIndex(['relate_type', 'relate_id'], ['name' => 'relate_type'])
            ->save();

        $this->execute('UPDATE files SET relate_type="Post" WHERE relate_type="";');
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $table = $this->table('files');
        $table
            ->renameColumn('relate_id', 'post_id')
            ->removeColumn('relate_type')
            ->addColumn('topic_id', 'integer')
            ->save();

        $table
            ->removeIndexByName('relate_type')
            ->addIndex('post_id')
            ->save();
    }
}
