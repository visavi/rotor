<?php

use Phinx\Migration\AbstractMigration;

class AddRatinglistToSetting extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $this->execute("INSERT INTO setting (name, value) VALUES ('ratinglist', 20);");
        $this->execute("DELETE FROM setting WHERE name='expiresrated' LIMIT 1;");
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->execute("DELETE FROM setting WHERE name='ratinglist' LIMIT 1;");
        $this->execute("INSERT INTO setting (name, value) VALUES ('expiresrated', 72);");
    }
}
