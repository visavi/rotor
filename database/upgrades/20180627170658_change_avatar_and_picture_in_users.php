<?php

use Phinx\Migration\AbstractMigration;

class ChangeAvatarAndPictureInUsers extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('users');
        $table->changeColumn('avatar', 'string', ['limit' => 100, 'null' => true])
            ->changeColumn('picture', 'string', ['limit' => 100, 'null' => true])
            ->save();

        $this->execute('UPDATE users SET avatar=concat("/uploads/avatars/", avatar) WHERE avatar IS NOT NULL;');
        $this->execute('UPDATE users SET picture=concat("/uploads/pictures/", picture) WHERE picture IS NOT NULL;');
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $table = $this->table('users');
        $table->changeColumn('avatar', 'string', ['limit' => 50, 'null' => true])
            ->changeColumn('picture', 'string', ['limit' => 50, 'null' => true])
            ->save();

        $this->execute('UPDATE users SET avatar=replace(avatar, "/uploads/avatars/", "") WHERE avatar IS NOT NULL;');
        $this->execute('UPDATE users SET picture=replace(picture, "/uploads/pictures/", "") WHERE picture IS NOT NULL;');
    }
}
