<?php

use Phinx\Migration\AbstractMigration;

class ChangeEditUserIdInPosts extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('posts');
        $table
            ->changeColumn('edit_user_id', 'integer', ['null' => true])
            ->save();

        $this->execute('UPDATE posts SET edit_user_id=null WHERE edit_user_id="0";');
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->execute('UPDATE posts SET edit_user_id="0" WHERE ISNULL(edit_user_id);');

        $table = $this->table('posts');
        $table
            ->changeColumn('edit_user_id', 'integer')
            ->save();
    }
}
