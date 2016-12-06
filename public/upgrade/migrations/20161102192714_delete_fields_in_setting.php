<?php

use Phinx\Migration\AbstractMigration;

class DeleteFieldsInSetting extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $this->execute("DELETE FROM setting WHERE setting_name='karantin' LIMIT 1;");
        $this->execute("DELETE FROM setting WHERE setting_name='cache' LIMIT 1;");
        $this->execute("DELETE FROM setting WHERE setting_name='navigation' LIMIT 1;");
        $this->execute("DELETE FROM setting WHERE setting_name='avatarupload' LIMIT 1;");
        $this->execute("DELETE FROM setting WHERE setting_name='avatarpoints' LIMIT 1;");
        $this->execute("DELETE FROM setting WHERE setting_name='avatarsize' LIMIT 1;");
        $this->execute("DELETE FROM setting WHERE setting_name='avatarweight' LIMIT 1;");
        $this->execute("DELETE FROM setting WHERE setting_name='avlist' LIMIT 1;");
        $this->execute("DELETE FROM setting WHERE setting_name='showlink' LIMIT 1;");
        $this->execute("DELETE FROM setting WHERE setting_name='gzip' LIMIT 1;");
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->execute("INSERT INTO setting (setting_name, setting_value) VALUES ('karantin', 0);");
        $this->execute("INSERT INTO setting (setting_name, setting_value) VALUES ('cache', 1);");
        $this->execute("INSERT INTO setting (setting_name, setting_value) VALUES ('navigation', 0);");
        $this->execute("INSERT INTO setting (setting_name, setting_value) VALUES ('avatarupload', 1000);");
        $this->execute("INSERT INTO setting (setting_name, setting_value) VALUES ('avatarpoints', 150);");
        $this->execute("INSERT INTO setting (setting_name, setting_value) VALUES ('avatarsize', 32);");
        $this->execute("INSERT INTO setting (setting_name, setting_value) VALUES ('avatarweight', 1024);");
        $this->execute("INSERT INTO setting (setting_name, setting_value) VALUES ('avlist', 10);");
        $this->execute("INSERT INTO setting (setting_name, setting_value) VALUES ('showlink', 10);");
        $this->execute("INSERT INTO setting (setting_name, setting_value) VALUES ('gzip', 0);");
    }
}
