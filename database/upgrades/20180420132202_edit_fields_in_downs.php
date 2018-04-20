<?php

use Phinx\Migration\AbstractMigration;

class EditFieldsInDowns extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('downs');
        $table
            ->changeColumn('category_id', 'integer')
            ->changeColumn('count_comments', 'integer', ['default' => 0])
            ->changeColumn('rating', 'integer', ['default' => 0])
            ->changeColumn('rated', 'integer', ['default' => 0])
            ->changeColumn('loads', 'integer', ['default' => 0])
            ->changeColumn('updated_at', 'integer', ['null' => true])
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
