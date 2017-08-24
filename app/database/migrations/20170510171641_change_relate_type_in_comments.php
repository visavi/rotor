<?php

use Phinx\Migration\AbstractMigration;

class ChangeRelateTypeInComments extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('comments');
        $table->changeColumn('relate_type', 'string', ['limit' => 20])
            ->save();

        $this->execute('UPDATE comments SET relate_type="Photo" WHERE relate_type="gallery";');
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $table = $this->table('comments');
        $table->changeColumn('relate_type', 'enum', ['values' => ['blog', 'down', 'news', 'offer', 'gallery']])
            ->save();

        $this->execute('UPDATE comments SET relate_type="gallery" WHERE relate_type="Photo";');
    }
}
