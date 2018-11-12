<?php

use App\Models\Message;
use Phinx\Migration\AbstractMigration;

class EditRelateTypeInSpam extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $this->execute('UPDATE spam SET relate_type="' . addslashes(Message::class) . '" WHERE relate_type = "' . addslashes('App\Models\Inbox') . '";');
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->execute('UPDATE spam SET relate_type="' . addslashes('App\Models\Inbox') . '" WHERE relate_type = "' . addslashes(Message::class) . '";');
    }
}
