<?php

use App\Models\Blog;
use App\Models\Down;
use App\Models\News;
use App\Models\Offer;
use App\Models\Photo;
use Phinx\Migration\AbstractMigration;

class ChangeRelateTypeInComments extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $this->execute('UPDATE comments SET relate_type="downs" WHERE relate_type="' . addslashes(Down::class) . '";');
        $this->execute('UPDATE comments SET relate_type="photos" WHERE relate_type="' . addslashes(Photo::class) . '";');
        $this->execute('UPDATE comments SET relate_type="articles" WHERE relate_type="' . addslashes(Blog::class) . '";');
        $this->execute('UPDATE comments SET relate_type="offers" WHERE relate_type="' . addslashes(Offer::class) . '";');
        $this->execute('UPDATE comments SET relate_type="news" WHERE relate_type="' . addslashes(News::class) . '";');

        $table = $this->table('comments');
        $table->changeColumn('relate_type', 'string', ['limit' => 10])
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $table = $this->table('comments');
        $table->changeColumn('relate_type', 'string', ['limit' => 50])
            ->save();

        $this->execute('UPDATE comments SET relate_type="' . addslashes(Down::class) . '" WHERE relate_type="downs";');
        $this->execute('UPDATE comments SET relate_type="' . addslashes(Photo::class) . '" WHERE relate_type="photos";');
        $this->execute('UPDATE comments SET relate_type="' . addslashes(Blog::class) . '" WHERE relate_type="articles";');
        $this->execute('UPDATE comments SET relate_type="' . addslashes(Offer::class) . '" WHERE relate_type="offers";');
        $this->execute('UPDATE comments SET relate_type="' . addslashes(News::class) . '" WHERE relate_type="news";');
    }
}
