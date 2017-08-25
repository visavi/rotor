<?php

use Phinx\Migration\AbstractMigration;

class DeleteMaxpostInSetting extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $this->execute("DELETE FROM setting WHERE name='maxblogcomm' LIMIT 1;");
        $this->execute("DELETE FROM setting WHERE name='maxdowncomm' LIMIT 1;");
        $this->execute("DELETE FROM setting WHERE name='maxkommnews' LIMIT 1;");
        $this->execute("DELETE FROM setting WHERE name='maxpostbook' LIMIT 1;");
        $this->execute("DELETE FROM setting WHERE name='maxpostchat' LIMIT 1;");
        $this->execute("DELETE FROM setting WHERE name='maxpostgallery' LIMIT 1;");
        $this->execute("DELETE FROM setting WHERE name='maxpostoffers' LIMIT 1;");
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->execute("INSERT INTO setting (name, value) VALUES ('maxblogcomm', 300);");
        $this->execute("INSERT INTO setting (name, value) VALUES ('maxdowncomm', 300);");
        $this->execute("INSERT INTO setting (name, value) VALUES ('maxkommnews', 500);");
        $this->execute("INSERT INTO setting (name, value) VALUES ('maxpostbook', 500);");
        $this->execute("INSERT INTO setting (name, value) VALUES ('maxpostchat', 500);");
        $this->execute("INSERT INTO setting (name, value) VALUES ('maxpostgallery', 100);");
        $this->execute("INSERT INTO setting (name, value) VALUES ('maxpostoffers', 300);");
    }
}
