<?php

use App\Models\Blog;
use App\Models\Down;
use App\Models\News;
use App\Models\Offer;
use App\Models\Photo;
use App\Models\Post;
use App\Models\Vote;
use Phinx\Migration\AbstractMigration;

class ChangeRelateTypeInPollings extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $this->execute('UPDATE pollings SET relate_type="articles" WHERE relate_type="' . addslashes(Blog::class) . '";');
        $this->execute('UPDATE pollings SET relate_type="downs" WHERE relate_type="' . addslashes(Down::class) . '";');
        $this->execute('UPDATE pollings SET relate_type="news" WHERE relate_type="' . addslashes(News::class) . '";');
        $this->execute('UPDATE pollings SET relate_type="offers" WHERE relate_type="' . addslashes(Offer::class) . '";');
        $this->execute('UPDATE pollings SET relate_type="photos" WHERE relate_type="' . addslashes(Photo::class) . '";');
        $this->execute('UPDATE pollings SET relate_type="posts" WHERE relate_type="' . addslashes(Post::class) . '";');
        $this->execute('UPDATE pollings SET relate_type="votes" WHERE relate_type="' . addslashes(Vote::class) . '";');

        $table = $this->table('pollings');
        $table->changeColumn('relate_type', 'string', ['limit' => 10])
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $table = $this->table('pollings');
        $table->changeColumn('relate_type', 'string', ['limit' => 50])
            ->save();

        $this->execute('UPDATE pollings SET relate_type="' . addslashes(Blog::class) . '" WHERE relate_type="articles";');
        $this->execute('UPDATE pollings SET relate_type="' . addslashes(Down::class) . '" WHERE relate_type="downs";');
        $this->execute('UPDATE pollings SET relate_type="' . addslashes(News::class) . '" WHERE relate_type="news";');
        $this->execute('UPDATE pollings SET relate_type="' . addslashes(Offer::class) . '" WHERE relate_type="offers";');
        $this->execute('UPDATE pollings SET relate_type="' . addslashes(Photo::class) . '" WHERE relate_type="photos";');
        $this->execute('UPDATE pollings SET relate_type="' . addslashes(Post::class) . '" WHERE relate_type="posts";');
        $this->execute('UPDATE pollings SET relate_type="' . addslashes(Vote::class) . '" WHERE relate_type="votes";');
    }
}
