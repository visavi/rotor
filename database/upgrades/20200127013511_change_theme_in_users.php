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
        $this->execute("UPDATE users SET themes='default';");
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
