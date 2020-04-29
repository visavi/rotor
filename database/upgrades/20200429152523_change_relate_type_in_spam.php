<?php

use App\Models\Comment;
use App\Models\Guestbook;
use App\Models\Message;
use App\Models\Post;
use App\Models\Wall;
use Phinx\Migration\AbstractMigration;

class ChangeRelateTypeInSpam extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $this->execute('UPDATE spam SET relate_type="posts" WHERE relate_type="' . addslashes(Post::class) . '";');
        $this->execute('UPDATE spam SET relate_type="guestbook" WHERE relate_type="' . addslashes(Guestbook::class) . '";');
        $this->execute('UPDATE spam SET relate_type="messages" WHERE relate_type="' . addslashes(Message::class) . '";');
        $this->execute('UPDATE spam SET relate_type="walls" WHERE relate_type="' . addslashes(Wall::class) . '";');
        $this->execute('UPDATE spam SET relate_type="comments" WHERE relate_type="' . addslashes(Comment::class) . '";');

        $table = $this->table('spam');
        $table->changeColumn('relate_type', 'string', ['limit' => 10])
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $table = $this->table('spam');
        $table->changeColumn('relate_type', 'string', ['limit' => 50])
            ->save();

        $this->execute('UPDATE spam SET relate_type="' . addslashes(Post::class) . '" WHERE relate_type="posts";');
        $this->execute('UPDATE spam SET relate_type="' . addslashes(Guestbook::class) . '" WHERE relate_type="guestbook";');
        $this->execute('UPDATE spam SET relate_type="' . addslashes(Message::class) . '" WHERE relate_type="messages";');
        $this->execute('UPDATE spam SET relate_type="' . addslashes(Wall::class) . '" WHERE relate_type="walls";');
        $this->execute('UPDATE spam SET relate_type="' . addslashes(Comment::class) . '" WHERE relate_type="comments";');
    }
}
