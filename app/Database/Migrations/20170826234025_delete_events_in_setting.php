<?php

use Phinx\Migration\AbstractMigration;

class DeleteEventsInSetting extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $this->execute("DELETE FROM setting WHERE name='postevents' LIMIT 1;");
        $this->execute("DELETE FROM setting WHERE name='eventpoint' LIMIT 1;");

        $this->execute("DELETE FROM comments WHERE relate_type='event'");
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->execute("INSERT INTO setting (name, value) VALUES ('postevents', 10);");
        $this->execute("INSERT INTO setting (name, value) VALUES ('eventpoint', 50);");
    }
}
