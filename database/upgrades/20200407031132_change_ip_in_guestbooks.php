<?php

use Intervention\Image\ImageManagerStatic as Image;
use Phinx\Migration\AbstractMigration;

class ChangeIpInGuestbooks extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up(): void
    {
        $table = $this->table('guestbooks');
        $table->addColumn('ip_new', 'varbinary', ['limit' => 16, 'after' => 'ip'])
            ->update();

        $this->execute("UPDATE guestbooks SET ip=if(ip NOT REGEXP '^((25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$', '127.0.0.1', ip);");
        $this->execute('UPDATE guestbooks SET ip_new=INET6_ATON(ip);');

        $table->removeColumn('ip')
            ->save();

        $table->renameColumn('ip_new', 'ip')
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down(): void
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
