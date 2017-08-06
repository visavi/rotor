<?php

use Phinx\Migration\AbstractMigration;

class ChangeLinkInSpam extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('spam');
        $table->renameColumn('link', 'path');
        $table->changeColumn('path', 'string', ['limit' => 100, 'null' => true])
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $table = $this->table('spam');
        $table->renameColumn('path', 'link');
        $table->changeColumn('link', 'string', ['limit' => 100])
            ->save();

        $this->execute('UPDATE comments SET relate_type="gallery" WHERE relate_type="Photo";');
    }
}
