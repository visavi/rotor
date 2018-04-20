<?php

use Phinx\Migration\AbstractMigration;

class EditFieldsInOffers extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('offers');
        $table
            ->changeColumn('rating', 'integer', ['default' => 0])
            ->changeColumn('count_comments', 'integer', ['default' => 0])
            ->changeColumn('updated_at', 'integer', ['null' => true])
            ->save();

        $table
            ->removeIndex('rating')
            ->addIndex('rating')
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
