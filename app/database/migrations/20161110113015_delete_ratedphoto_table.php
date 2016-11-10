<?php

use Phinx\Migration\AbstractMigration;

class DeleteRatedphotoTable extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $this->dropTable('ratedphoto');
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->execute("CREATE TABLE IF NOT EXISTS `ratedphoto` (
          `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
          `photo` int(11) unsigned NOT NULL,
          `user` varchar(20) NOT NULL,
          `time` int(11) unsigned NOT NULL,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4;");
    }
}
