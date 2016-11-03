<?php

use Phinx\Migration\AbstractMigration;

class DeleteAvatarsTable extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $this->dropTable('avatars');
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->execute("CREATE TABLE IF NOT EXISTS `avatars` (
          `avatars_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
          `avatars_cats` smallint(4) unsigned NOT NULL,
          `avatars_name` varchar(20) NOT NULL,
          PRIMARY KEY (`avatars_id`),
          KEY `avatars_cats` (`avatars_cats`)
        ) ENGINE=InnoDb  DEFAULT CHARSET=utf8mb4;");
    }
}
