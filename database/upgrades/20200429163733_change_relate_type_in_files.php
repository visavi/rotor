<?php

use App\Models\Blog;
use App\Models\Down;
use App\Models\Item;
use App\Models\Photo;
use App\Models\Post;
use Phinx\Migration\AbstractMigration;

class ChangeRelateTypeInFiles extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $this->execute('UPDATE files SET relate_type="articles" WHERE relate_type="' . addslashes(Blog::class) . '";');
        $this->execute('UPDATE files SET relate_type="downs" WHERE relate_type="' . addslashes(Down::class) . '";');
        $this->execute('UPDATE files SET relate_type="items" WHERE relate_type="' . addslashes(Item::class) . '";');
        $this->execute('UPDATE files SET relate_type="photos" WHERE relate_type="' . addslashes(Photo::class) . '";');
        $this->execute('UPDATE files SET relate_type="posts" WHERE relate_type="' . addslashes(Post::class) . '";');

        $table = $this->table('files');
        $table->changeColumn('relate_type', 'string', ['limit' => 10])
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $table = $this->table('files');
        $table->changeColumn('relate_type', 'string', ['limit' => 50])
            ->save();

        $this->execute('UPDATE files SET relate_type="' . addslashes(Blog::class) . '" WHERE relate_type="articles";');
        $this->execute('UPDATE files SET relate_type="' . addslashes(Down::class) . '" WHERE relate_type="downs";');
        $this->execute('UPDATE files SET relate_type="' . addslashes(Item::class) . '" WHERE relate_type="items";');
        $this->execute('UPDATE files SET relate_type="' . addslashes(Photo::class) . '" WHERE relate_type="photos";');
        $this->execute('UPDATE files SET relate_type="' . addslashes(Post::class) . '" WHERE relate_type="posts";');
    }
}
