<?php

use Phinx\Migration\AbstractMigration;

class AddBlogCreateToSettings extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $this->execute("INSERT INTO settings (name, value) VALUES ('blog_create', 1);");
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->execute("DELETE FROM settings WHERE name='blog_create' LIMIT 1;");
    }
}
