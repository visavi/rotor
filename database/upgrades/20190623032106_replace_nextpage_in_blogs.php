<?php

use Phinx\Migration\AbstractMigration;

class ReplaceNextpageInBlogs extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $this->execute("UPDATE blogs SET text = replace(`text`, '[nextpage]', '')");
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
