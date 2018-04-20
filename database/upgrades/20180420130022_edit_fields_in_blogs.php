<?php

use Phinx\Migration\AbstractMigration;

class EditFieldsInBlogs extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('blogs');
        $table
            ->changeColumn('category_id', 'integer')
            ->changeColumn('rating', 'integer', ['default' => 0])
            ->changeColumn('visits', 'integer', ['default' => 0])
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
