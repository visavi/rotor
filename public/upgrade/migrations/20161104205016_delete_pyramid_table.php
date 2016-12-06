<?php

use Phinx\Migration\AbstractMigration;

class DeletePyramidTable extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $this->dropTable('pyramid');
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->execute("CREATE TABLE IF NOT EXISTS `pyramid` (
          `pyramid_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
          `pyramid_link` varchar(50) NOT NULL,
          `pyramid_name` varchar(50) NOT NULL,
          `pyramid_user` varchar(20) NOT NULL,
          PRIMARY KEY (`pyramid_id`)
        ) ENGINE=InnoDb  DEFAULT CHARSET=utf8mb4;");
    }
}
