<?php

use Phinx\Migration\AbstractMigration;

class ChangeFieldsInUsers extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $users = $this->table('users');
        $users
            ->changeColumn('joined', 'integer')
            ->changeColumn('nickname', 'string', ['limit' => 20, 'null' => true])
            ->changeColumn('name', 'string', ['limit' => 20, 'null' => true])
            ->changeColumn('country', 'string', ['limit' => 30, 'null' => true])
            ->changeColumn('city', 'string', ['limit' => 50, 'null' => true])
            ->changeColumn('info', 'text', ['null' => true])
            ->changeColumn('site', 'string', ['limit' => 50, 'null' => true])
            ->changeColumn('icq', 'string', ['limit' => 10, 'null' => true])
            ->changeColumn('skype', 'string', ['limit' => 32, 'null' => true])
            ->changeColumn('gender', 'boolean', ['default' => 0])
            ->changeColumn('birthday', 'string', ['limit' => 10, 'null' => true])
            ->changeColumn('themes', 'string', ['limit' => 20, 'null' => true])
            ->changeColumn('timeban', 'integer', ['default' => 0])
            ->changeColumn('timelastban', 'integer', ['default' => 0])
            ->changeColumn('reasonban', 'text', ['null' => true])
            ->changeColumn('loginsendban', 'string', ['limit' => 20, 'null' => true])
            ->changeColumn('status', 'string', ['limit' => 50, 'null' => true])
            ->changeColumn('avatar', 'string', ['limit' => 50, 'null' => true])
            ->changeColumn('picture', 'string', ['limit' => 50, 'null' => true])
            ->changeColumn('keypasswd', 'string', ['limit' => 20, 'null' => true])
            ->changeColumn('timepasswd', 'integer', ['default' => 0])
            ->changeColumn('timelastlogin', 'integer', ['default' => 0])
            ->changeColumn('sendprivatmail', 'boolean', ['default' => 0])
            ->changeColumn('confirmreg', 'boolean', ['default' => 0])
            ->changeColumn('confirmregkey', 'string', ['limit' => 30, 'null' => true])
            ->changeColumn('secquest', 'string', ['limit' => 50, 'null' => true])
            ->changeColumn('secanswer', 'string', ['limit' => 40, 'null' => true])
            ->changeColumn('timenickname', 'integer', ['default' => 0])
            ->changeColumn('ipbinding', 'boolean', ['default' => 0])
            ->changeColumn('privacy', 'boolean', ['default' => 0])
            ->changeColumn('apikey', 'string', ['limit' => 32, 'null' => true])
            ->changeColumn('subscribe', 'string', ['limit' => 32, 'null' => true])
            ->save();

            if ($users->hasColumn('users_sumcredit')) {
                $users->changeColumn('timecredit', 'integer', ['default' => 0])
                    ->save();
            }
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
