<?php

use Phinx\Migration\AbstractMigration;

class ChangeRelateTypeInPollings extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up(): void
    {
        $this->execute('UPDATE pollings SET relate_type="articles" WHERE relate_type="App\\Models\\Blog";');
        $this->execute('UPDATE pollings SET relate_type="downs" WHERE relate_type="App\\Models\\Down";');
        $this->execute('UPDATE pollings SET relate_type="news" WHERE relate_type="App\\Models\\News";');
        $this->execute('UPDATE pollings SET relate_type="offers" WHERE relate_type="App\\Models\\Offer";');
        $this->execute('UPDATE pollings SET relate_type="photos" WHERE relate_type="App\\Models\\Photo";');
        $this->execute('UPDATE pollings SET relate_type="posts" WHERE relate_type="App\\Models\\Post";');
        $this->execute('UPDATE pollings SET relate_type="votes" WHERE relate_type="App\\Models\\Vote";');

        $table = $this->table('pollings');
        $table->changeColumn('relate_type', 'string', ['limit' => 10])
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down(): void
    {
        $table = $this->table('pollings');
        $table->changeColumn('relate_type', 'string', ['limit' => 50])
            ->save();

        $this->execute('UPDATE pollings SET relate_type="App\\Models\\Blog" WHERE relate_type="articles";');
        $this->execute('UPDATE pollings SET relate_type="App\\Models\\Down" WHERE relate_type="downs";');
        $this->execute('UPDATE pollings SET relate_type="App\\Models\\News" WHERE relate_type="news";');
        $this->execute('UPDATE pollings SET relate_type="App\\Models\\Offer" WHERE relate_type="offers";');
        $this->execute('UPDATE pollings SET relate_type="App\\Models\\Photo" WHERE relate_type="photos";');
        $this->execute('UPDATE pollings SET relate_type="App\\Models\\Post" WHERE relate_type="posts";');
        $this->execute('UPDATE pollings SET relate_type="App\\Models\\Vote" WHERE relate_type="votes";');
    }
}
