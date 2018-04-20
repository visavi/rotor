<?php

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

class CreateUsersTable extends AbstractMigration
{
    /**
     * Change Method.
     */
    public function change()
    {
        if (! $this->hasTable('users')) {
            $table = $this->table('users', ['collation' => env('DB_COLLATION')]);
            $table
                ->addColumn('login', 'string', ['limit' => 20])
                ->addColumn('password', 'string', ['limit' => 128])
                ->addColumn('email', 'string', ['limit' => 50])
                ->addColumn('level', 'string', ['limit' => 20, 'default' => 'guest'])
                ->addColumn('name', 'string', ['limit' => 20, 'null' => true])
                ->addColumn('country', 'string', ['limit' => 30, 'null' => true])
                ->addColumn('city', 'string', ['limit' => 50, 'null' => true])
                ->addColumn('lang', 'string', ['limit' => 2, 'null' => true])
                ->addColumn('info', 'text', ['null' => true])
                ->addColumn('site', 'string', ['limit' => 50, 'null' => true])
                ->addColumn('icq', 'string', ['limit' => 10, 'null' => true])
                ->addColumn('skype', 'string', ['limit' => 32, 'null' => true])
                ->addColumn('gender', 'enum', ['values' => ['male','female']])
                ->addColumn('birthday', 'string', ['limit' => 10, 'null' => true])
                ->addColumn('visits', 'integer', ['default' => 0])
                ->addColumn('newprivat', 'integer', ['default' => 0])
                ->addColumn('newwall', 'integer', ['default' => 0])
                ->addColumn('allforum', 'integer', ['default' => 0])
                ->addColumn('allguest', 'integer', ['default' => 0])
                ->addColumn('allcomments', 'integer', ['default' => 0])
                ->addColumn('themes', 'string', ['limit' => 20, 'null' => true])
                ->addColumn('timezone', 'string', ['limit' => 3, 'default' => 0])
                ->addColumn('point', 'integer', ['default' => 0])
                ->addColumn('money', 'integer', ['default' => 0])
                ->addColumn('timeban', 'integer', ['null' => true])
                ->addColumn('status', 'string', ['limit' => 50, 'null' => true])
                ->addColumn('avatar', 'string', ['limit' => 50, 'null' => true])
                ->addColumn('picture', 'string', ['limit' => 50, 'null' => true])
                ->addColumn('rating', 'integer', ['default' => 0])
                ->addColumn('posrating', 'integer', ['default' => 0])
                ->addColumn('negrating', 'integer', ['default' => 0])
                ->addColumn('keypasswd', 'string', ['limit' => 20, 'null' => true])
                ->addColumn('timepasswd', 'integer', ['default' => 0])
                ->addColumn('sendprivatmail', 'boolean', ['default' => 0])
                ->addColumn('timebonus', 'integer', ['default' => 0])
                ->addColumn('confirmregkey', 'string', ['limit' => 30, 'null' => true])
                ->addColumn('newchat', 'integer', ['null' => true])
                ->addColumn('notify', 'boolean', ['default' => 1])
                ->addColumn('apikey', 'string', ['limit' => 32, 'null' => true])
                ->addColumn('subscribe', 'string', ['limit' => 32, 'null' => true])
                ->addColumn('updated_at', 'integer', ['null' => true])
                ->addColumn('created_at', 'integer')
                ->addIndex('email', ['unique' => true])
                ->addIndex('login', ['unique' => true])
                ->addIndex('level')
                ->addIndex('themes')
                ->addIndex('point')
                ->addIndex('money')
                ->addIndex('rating')
                ->create();
        }
    }
}
