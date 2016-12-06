<?php

use Phinx\Migration\AbstractMigration;

class DeleteRatedblogTable extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $this->dropTable('ratedblog');
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->execute("CREATE TABLE IF NOT EXISTS `ratedblog` (
          `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
          `blog` int(11) unsigned NOT NULL,
          `user` varchar(20) NOT NULL,
          `time` int(11) unsigned NOT NULL,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4;");
    }
}
