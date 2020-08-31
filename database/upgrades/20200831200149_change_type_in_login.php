<?php

use App\Models\Login;
use Phinx\Migration\AbstractMigration;

class ChangeTypeInLogin extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up(): void
    {
        $table = $this->table('login');
        $table->changeColumn('type', 'string', ['limit' => 10])
            ->save();

        $this->execute("UPDATE login SET type = '". Login::AUTH . "' where type = '1'");
        $this->execute("UPDATE login SET type = '". Login::COOKIE . "' where type = '0'");
    }

    /**
     * Migrate Down.
     */
    public function down(): void
    {
        $this->execute("UPDATE login SET type = 1 where type = '". Login::AUTH . "'");
        $this->execute("UPDATE login SET type = 0 where type = '". Login::COOKIE . "'");
        $this->execute("UPDATE login SET type = 1 where type = '". Login::SOCIAL . "'");

        $table = $this->table('login');
        $table->changeColumn('type', 'boolean', ['default' => 0])
            ->save();
    }
}
