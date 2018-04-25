<?php

use Phinx\Migration\AbstractMigration;

class RenamePhotoTable extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('photo');
        $table->rename('photos');
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $table = $this->table('photos');
        $table->rename('photo');
    }
}
