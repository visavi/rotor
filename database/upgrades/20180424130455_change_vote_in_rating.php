<?php

use Phinx\Migration\AbstractMigration;

class ChangeVoteInRating extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('rating');
        $table
            ->changeColumn('vote', 'string', ['limit' => 5])
            ->save();

        $this->execute('UPDATE rating SET vote="+" WHERE vote = "1";');
        $this->execute('UPDATE rating SET vote="-" WHERE vote = "0";');

        $table
            ->changeColumn('vote', 'string', ['limit' => 1])
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $table = $this->table('rating');

        $table
            ->changeColumn('vote', 'string', ['limit' => 5])
            ->save();

        $this->execute('UPDATE rating SET vote="1" WHERE vote = "+";');
        $this->execute('UPDATE rating SET vote="0" WHERE vote = "-";');

        $table
            ->changeColumn('vote', 'boolean', ['default' => true])
            ->save();
    }
}
