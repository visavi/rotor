<?php

use App\Models\Blog;
use Phinx\Migration\AbstractMigration;

class ChangeRelateInReads extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('reads');
        $table->changeColumn('relate_type', 'string', ['limit' => 50])
            ->save();

        $this->execute('UPDATE `reads` SET relate_type="'.addslashes(Blog::class).'" WHERE relate_type="Blog";');
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $table = $this->table('reads');
        $table->changeColumn('relate_type', 'string', ['limit' => 20])
            ->save();

        $this->execute('UPDATE `reads` SET relate_type="Blog" WHERE relate_type="'.addslashes(Blog::class).'";');
    }
}
