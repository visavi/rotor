<?php

use App\Models\Blog;
use App\Models\Down;
use App\Models\News;
use App\Models\Offer;
use App\Models\Photo;
use App\Models\Post;
use Phinx\Migration\AbstractMigration;

class ChangeRelateInPollings extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('pollings');
        $table->changeColumn('relate_type', 'string', ['limit' => 50])
            ->save();

        $this->execute('UPDATE pollings SET relate_type="'.addslashes(Photo::class).'" WHERE relate_type="Gallery";');
        $this->execute('UPDATE pollings SET relate_type="'.addslashes(Photo::class).'" WHERE relate_type="Photo";');
        $this->execute('UPDATE pollings SET relate_type="'.addslashes(Blog::class).'" WHERE relate_type="Blog";');
        $this->execute('UPDATE pollings SET relate_type="'.addslashes(News::class).'" WHERE relate_type="News";');
        $this->execute('UPDATE pollings SET relate_type="'.addslashes(Offer::class).'" WHERE relate_type="Offer";');
        $this->execute('UPDATE pollings SET relate_type="'.addslashes(Down::class).'" WHERE relate_type="Down";');
        $this->execute('UPDATE pollings SET relate_type="'.addslashes(Post::class).'" WHERE relate_type="Post";');
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $table = $this->table('pollings');
        $table->changeColumn('relate_type', 'string', ['limit' => 20])
            ->save();

        $this->execute('UPDATE pollings SET relate_type="Photo" WHERE relate_type="'.addslashes(Photo::class).'";');
        $this->execute('UPDATE pollings SET relate_type="Blog" WHERE relate_type="'.addslashes(Blog::class).'";');
        $this->execute('UPDATE pollings SET relate_type="News" WHERE relate_type="'.addslashes(News::class).'";');
        $this->execute('UPDATE pollings SET relate_type="Offer" WHERE relate_type="'.addslashes(Offer::class).'";');
        $this->execute('UPDATE pollings SET relate_type="Down" WHERE relate_type="'.addslashes(Down::class).'";');
        $this->execute('UPDATE pollings SET relate_type="Post" WHERE relate_type="'.addslashes(Post::class).'";');
    }
}
