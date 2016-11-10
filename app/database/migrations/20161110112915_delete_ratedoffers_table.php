<?php

use Phinx\Migration\AbstractMigration;

class DeleteRatedoffersTable extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $this->dropTable('ratedoffers');
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->execute("CREATE TABLE IF NOT EXISTS `ratedoffers` (
          `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
          `offers` smallint(4) unsigned NOT NULL,
          `user` varchar(20) NOT NULL,
          `time` int(11) unsigned NOT NULL,
          PRIMARY KEY (`id`),
          KEY `user` (`user`)
        ) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4;");
    }
}
