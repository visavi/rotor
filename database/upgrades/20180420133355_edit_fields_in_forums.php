<?php

use Phinx\Migration\AbstractMigration;

class EditFieldsInForums extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('forums');
        $table
            ->changeColumn('sort', 'integer', ['default' => 0])
            ->changeColumn('parent_id', 'integer', ['default' => 0])
            ->changeColumn('last_topic_id', 'integer', ['default' => 0])
            ->changeColumn('count_topics', 'integer', ['default' => 0])
            ->changeColumn('count_posts', 'integer', ['default' => 0])
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
