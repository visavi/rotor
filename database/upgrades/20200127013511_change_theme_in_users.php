<?php

use Intervention\Image\ImageManagerStatic as Image;
use Phinx\Migration\AbstractMigration;

class ChangeThemeInUsers extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $this->execute("UPDATE users SET themes='mobile' WHERE themes='default';");
        $this->execute("UPDATE users SET themes='default' WHERE themes<>'mobile' AND themes<>'motor';");
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
