<?php

use Phinx\Migration\AbstractMigration;

class EditFieldsInNews extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('news');
        $table
            ->changeColumn('count_comments', 'integer', ['default' => 0])
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
