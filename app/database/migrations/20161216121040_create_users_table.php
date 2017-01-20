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
        $table = $this->table('users', ['engine' => 'MyISAM',  'collation' => 'utf8mb4_unicode_ci']);
        $table->addColumn('login', 'string', ['limit' => 20])
            ->addColumn('password', 'string', ['limit' => 128])
            ->addColumn('email', 'string', ['limit' => 50])
            ->addColumn('joined', 'integer')
            ->addColumn('level', 'integer', ['limit' => MysqlAdapter::INT_SMALL, 'signed' => false, 'default' => 107])
            ->addColumn('nickname', 'string', ['limit' => 20, 'null' => true])
            ->addColumn('name', 'string', ['limit' => 20, 'null' => true])
            ->addColumn('country', 'string', ['limit' => 30, 'null' => true])
            ->addColumn('city', 'string', ['limit' => 50, 'null' => true])
            ->addColumn('info', 'text', ['null' => true])
            ->addColumn('site', 'string', ['limit' => 50, 'null' => true])
            ->addColumn('icq', 'string', ['limit' => 10, 'null' => true])
            ->addColumn('skype', 'string', ['limit' => 32, 'null' => true])
            ->addColumn('gender', 'boolean', ['default' => false])
            ->addColumn('birthday', 'string', ['limit' => 10, 'null' => true])
            ->addColumn('visits', 'integer', ['signed' => false, 'default' => 0])
            ->addColumn('newprivat', 'integer', ['limit' => MysqlAdapter::INT_SMALL, 'signed' => false, 'default' => 0])
            ->addColumn('newwall', 'integer', ['limit' => MysqlAdapter::INT_SMALL, 'signed' => false, 'default' => 0])
            ->addColumn('allforum', 'integer', ['signed' => false, 'default' => 0])
            ->addColumn('allguest', 'integer', ['signed' => false, 'default' => 0])
            ->addColumn('allcomments', 'integer', ['signed' => false, 'default' => 0])
            ->addColumn('themes', 'string', ['limit' => 20, 'null' => true])
            ->addColumn('timezone', 'string', ['limit' => 3, 'default' => 0])
            ->addColumn('point', 'integer', ['signed' => false, 'default' => 0])
            ->addColumn('money', 'integer', ['signed' => false, 'default' => 0])
            ->addColumn('ban', 'boolean', ['signed' => false, 'default' => false])
            ->addColumn('timeban', 'integer', ['default' => 0])
            ->addColumn('timelastban', 'integer', ['default' => 0])
            ->addColumn('reasonban', 'text', ['null' => true])
            ->addColumn('loginsendban', 'string', ['limit' => 20, 'null' => true])
            ->addColumn('totalban', 'boolean', ['signed' => false, 'default' => false])
            ->addColumn('explainban', 'boolean', ['signed' => false, 'default' => false])
            ->addColumn('status', 'string', ['limit' => 50, 'null' => true])
            ->addColumn('avatar', 'string', ['limit' => 50, 'null' => true])
            ->addColumn('picture', 'string', ['limit' => 50, 'null' => true])
            ->addColumn('rating', 'integer', ['limit' => MysqlAdapter::INT_MEDIUM, 'default' => 0])
            ->addColumn('posrating', 'integer', ['limit' => MysqlAdapter::INT_MEDIUM, 'signed' => false, 'default' => 0])
            ->addColumn('negrating', 'integer', ['limit' => MysqlAdapter::INT_MEDIUM, 'signed' => false, 'default' => 0])
            ->addColumn('keypasswd', 'string', ['limit' => 20, 'null' => true])
            ->addColumn('timepasswd', 'integer', ['default' => 0])
            ->addColumn('timelastlogin', 'integer', ['default' => 0])
            ->addColumn('timebonus', 'integer', ['default' => 0])
            ->addColumn('sendprivatmail', 'boolean', ['default' => false])
            ->addColumn('confirmreg', 'boolean', ['default' => false])
            ->addColumn('confirmregkey', 'string', ['limit' => 30, 'null' => true])
            ->addColumn('secquest', 'string', ['limit' => 50, 'null' => true])
            ->addColumn('secanswer', 'string', ['limit' => 40, 'null' => true])
            ->addColumn('timenickname', 'integer', ['default' => 0])
            ->addColumn('newchat', 'integer', ['signed' => false, 'default' => 0])
            ->addColumn('privacy', 'boolean', ['default' => false])
            ->addColumn('notify', 'boolean', ['default' => true])
            ->addColumn('apikey', 'string', ['limit' => 32, 'null' => true])
            ->addColumn('subscribe', 'string', ['limit' => 32, 'null' => true])
            ->addColumn('sumcredit', 'integer', ['signed' => false, 'default' => 0])
            ->addColumn('timecredit', 'integer', ['default' => 0])
            ->addIndex('email', ['unique' => true])
            ->addIndex('login', ['unique' => true])
            ->addIndex('level')
            ->addIndex('nickname')
            ->addIndex('themes')
            ->addIndex('point')
            ->addIndex('money')
            ->addIndex('rating')
            ->create();
    }
}
