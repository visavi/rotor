<?php

use App\Models\Post;
use Phinx\Migration\AbstractMigration;

class ChangeRelateInFiles extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('files');
        $table->changeColumn('relate_type', 'string', ['limit' => 50])
            ->save();

        $this->execute('UPDATE files SET relate_type="'.addslashes(Post::class).'" WHERE relate_type="Post";');
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $table = $this->table('files');
        $table->changeColumn('relate_type', 'string', ['limit' => 20])
            ->save();

        $this->execute('UPDATE files SET relate_type="Post" WHERE relate_type="'.addslashes(Post::class).'";');
    }
}
