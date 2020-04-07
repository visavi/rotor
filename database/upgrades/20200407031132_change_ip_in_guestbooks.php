<?php

use Intervention\Image\ImageManagerStatic as Image;
use Phinx\Migration\AbstractMigration;

class ChangeIpInGuestbooks extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('guestbooks');
        $table->addColumn('ip_new', 'varbinary', ['limit' => 16, 'after' => 'ip'])
            ->update();

        $this->execute('UPDATE guestbooks SET ip_new=INET6_ATON(ip);');

        $table->removeColumn('ip')
            ->save();

        $table->renameColumn('ip_new', 'ip')
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $table = $this->table('guestbooks');
        $table->addColumn('ip_new', 'string', ['limit' => 15, 'after' => 'ip'])
            ->update();

        $this->execute('UPDATE guestbooks SET ip_new=INET6_NTOA(ip);');


        $table->removeColumn('ip')
            ->save();

        $table->renameColumn('ip_new', 'ip')
            ->save();
    }
}
