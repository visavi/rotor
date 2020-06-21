<?php

use Phinx\Migration\AbstractMigration;

class ChangeRelateTypeInFiles extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up(): void
    {
        $this->execute('UPDATE files SET relate_type="articles" WHERE relate_type="App\\Models\\Blog";');
        $this->execute('UPDATE files SET relate_type="downs" WHERE relate_type="App\\Models\\Down";');
        $this->execute('UPDATE files SET relate_type="items" WHERE relate_type="App\\Models\\Item";');
        $this->execute('UPDATE files SET relate_type="photos" WHERE relate_type="App\\Models\\Photo";');
        $this->execute('UPDATE files SET relate_type="posts" WHERE relate_type="App\\Models\\Post";');

        $table = $this->table('files');
        $table->changeColumn('relate_type', 'string', ['limit' => 10])
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down(): void
    {
        $table = $this->table('files');
        $table->changeColumn('relate_type', 'string', ['limit' => 50])
            ->save();

        $this->execute('UPDATE files SET relate_type="App\\Models\\Blog" WHERE relate_type="articles";');
        $this->execute('UPDATE files SET relate_type="App\\Models\\Down" WHERE relate_type="downs";');
        $this->execute('UPDATE files SET relate_type="App\\Models\\Item" WHERE relate_type="items";');
        $this->execute('UPDATE files SET relate_type="App\\Models\\Photo" WHERE relate_type="photos";');
        $this->execute('UPDATE files SET relate_type="App\\Models\\Post" WHERE relate_type="posts";');
    }
}
