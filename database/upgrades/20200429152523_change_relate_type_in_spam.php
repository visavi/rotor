<?php

use Phinx\Migration\AbstractMigration;

class ChangeRelateTypeInSpam extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up(): void
    {
        $this->execute('UPDATE spam SET relate_type="posts" WHERE relate_type LIKE "%Post";');
        $this->execute('UPDATE spam SET relate_type="guestbook" WHERE relate_type LIKE "%Guestbook";');
        $this->execute('UPDATE spam SET relate_type="messages" WHERE relate_type LIKE "%Message";');
        $this->execute('UPDATE spam SET relate_type="walls" WHERE relate_type LIKE "%Wall";');
        $this->execute('UPDATE spam SET relate_type="comments" WHERE relate_type LIKE "%Comment";');

        $table = $this->table('spam');
        $table->changeColumn('relate_type', 'string', ['limit' => 10])
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down(): void
    {
        $table = $this->table('spam');
        $table->changeColumn('relate_type', 'string', ['limit' => 50])
            ->save();

        $this->execute('UPDATE spam SET relate_type="App\\Models\\Post" WHERE relate_type="posts";');
        $this->execute('UPDATE spam SET relate_type="App\\Models\\Guestbook" WHERE relate_type="guestbook";');
        $this->execute('UPDATE spam SET relate_type="App\\Models\\Message" WHERE relate_type="messages";');
        $this->execute('UPDATE spam SET relate_type="App\\Models\\Wall" WHERE relate_type="walls";');
        $this->execute('UPDATE spam SET relate_type="App\\Models\\Comment" WHERE relate_type="comments";');
    }
}
