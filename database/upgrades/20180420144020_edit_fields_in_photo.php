<?php

use Phinx\Migration\AbstractMigration;

class EditFieldsInPhoto extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('photo');
        $table
            ->changeColumn('rating', 'integer', ['default' => 0])
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
