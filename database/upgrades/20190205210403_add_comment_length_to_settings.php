<?php

use Phinx\Migration\AbstractMigration;

class AddCommentLengthToSettings extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $this->execute("INSERT INTO settings (name, value) VALUES ('comment_length', 1000);");
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->execute("DELETE FROM settings WHERE name='comment_length' LIMIT 1;");
    }
}
