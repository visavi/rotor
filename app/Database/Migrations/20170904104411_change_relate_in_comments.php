<?php

use App\Models\Blog;
use App\Models\Down;
use App\Models\News;
use App\Models\Offer;
use App\Models\Photo;
use Phinx\Migration\AbstractMigration;

class ChangeRelateInComments extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('comments');
        $table->changeColumn('relate_type', 'string', ['limit' => 50])
            ->save();

        $this->execute('UPDATE comments SET relate_type="'.addslashes(Photo::class).'" WHERE relate_type="Photo";');
        $this->execute('UPDATE comments SET relate_type="'.addslashes(Blog::class).'" WHERE relate_type="Blog";');
        $this->execute('UPDATE comments SET relate_type="'.addslashes(News::class).'" WHERE relate_type="News";');
        $this->execute('UPDATE comments SET relate_type="'.addslashes(Offer::class).'" WHERE relate_type="Offer";');
        $this->execute('UPDATE comments SET relate_type="'.addslashes(Down::class).'" WHERE relate_type="Down";');
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $table = $this->table('comments');
        $table->changeColumn('relate_type', 'string', ['limit' => 20])
            ->save();

        $this->execute('UPDATE comments SET relate_type="Photo" WHERE relate_type="'.addslashes(Photo::class).'";');
        $this->execute('UPDATE comments SET relate_type="Blog" WHERE relate_type="'.addslashes(Blog::class).'";');
        $this->execute('UPDATE comments SET relate_type="News" WHERE relate_type="'.addslashes(News::class).'";');
        $this->execute('UPDATE comments SET relate_type="Offer" WHERE relate_type="'.addslashes(Offer::class).'";');
        $this->execute('UPDATE comments SET relate_type="Down" WHERE relate_type="'.addslashes(Down::class).'";');
    }
}
