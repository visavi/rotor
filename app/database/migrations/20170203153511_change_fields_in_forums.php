<?php

use Phinx\Migration\AbstractMigration;

class ChangeFieldsInForums extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('forums');
        $table
            ->removeColumn('last_themes')
            ->removeColumn('last_user')
            ->removeColumn('last_time')
            ->renameColumn('last_id', 'last_topic_id')
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $table = $this->table('forums');
        $table
            ->addColumn('last_themes', 'string', ['limit' => 50, 'null' => true])
            ->addColumn('last_user', 'string', ['limit' => 20, 'null' => true])
            ->addColumn('last_time', 'integer', ['signed' => false, 'default' => 0])
            ->renameColumn('last_topic_id', 'last_id')
            ->save();
    }
}
