<?php

use Phinx\Migration\AbstractMigration;

class ChangeRelateTypeInReaders extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up(): void
    {
        $this->execute('UPDATE readers SET relate_type="articles" WHERE relate_type="App\\Models\\Blog";');
        $this->execute('UPDATE readers SET relate_type="downs" WHERE relate_type="App\\Models\\Down";');
        $this->execute('UPDATE readers SET relate_type="topics" WHERE relate_type="App\\Models\\Topic";');

        $table = $this->table('readers');
        $table->changeColumn('relate_type', 'string', ['limit' => 10])
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down(): void
    {
        $table = $this->table('readers');
        $table->changeColumn('relate_type', 'string', ['limit' => 50])
            ->save();

        $this->execute('UPDATE readers SET relate_type="App\\Models\\Blog" WHERE relate_type="articles";');
        $this->execute('UPDATE readers SET relate_type="App\\Models\\Down" WHERE relate_type="downs";');
        $this->execute('UPDATE readers SET relate_type="App\\Models\\Topic" WHERE relate_type="topics";');
    }
}
