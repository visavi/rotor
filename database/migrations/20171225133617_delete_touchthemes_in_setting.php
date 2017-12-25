<?php

use Phinx\Migration\AbstractMigration;

class DeleteTouchthemesInSetting extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $this->execute("DELETE FROM setting WHERE name='touchthemes' LIMIT 1;");
        $this->execute("DELETE FROM setting WHERE name='photoexprated' LIMIT 1;");
        $this->execute("DELETE FROM setting WHERE name='blogexpread' LIMIT 1;");
        $this->execute("DELETE FROM setting WHERE name='blogexprated' LIMIT 1;");
        $this->execute("DELETE FROM setting WHERE name='showuser' LIMIT 1;");
        $this->execute("DELETE FROM setting WHERE name='lastusers' LIMIT 1;");
        $this->execute("DELETE FROM setting WHERE name='lifelist' LIMIT 1;");
        $this->execute("DELETE FROM setting WHERE name='proxy' LIMIT 1;");
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->execute("INSERT INTO setting (name, value) VALUES ('touchthemes', 'default');");
        $this->execute("INSERT INTO setting (name, value) VALUES ('photoexprated', '72');");
        $this->execute("INSERT INTO setting (name, value) VALUES ('blogexpread', '72');");
        $this->execute("INSERT INTO setting (name, value) VALUES ('blogexprated', '72');");
        $this->execute("INSERT INTO setting (name, value) VALUES ('showuser', '10');");
        $this->execute("INSERT INTO setting (name, value) VALUES ('lastusers', '100');");
        $this->execute("INSERT INTO setting (name, value) VALUES ('lifelist', '10');");
        $this->execute("INSERT INTO setting (name, value) VALUES ('proxy', '');");
    }
}
