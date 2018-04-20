<?php

use Phinx\Migration\AbstractMigration;

class EditFieldsInCategories extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('categories');
        $table
            ->changeColumn('sort', 'integer', ['default' => 0])
            ->changeColumn('parent_id', 'integer', ['default' => 0])
            ->changeColumn('count_blogs', 'integer', ['default' => 0])
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
