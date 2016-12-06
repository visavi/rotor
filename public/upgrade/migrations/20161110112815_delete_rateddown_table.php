<?php

use Phinx\Migration\AbstractMigration;

class DeleteRateddownTable extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $this->dropTable('rateddown');
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->execute("CREATE TABLE IF NOT EXISTS `rateddown` (
          `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
          `down` mediumint(8) unsigned NOT NULL,
          `user` varchar(20) NOT NULL,
          `time` int(11) unsigned NOT NULL,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4;");
    }
}
