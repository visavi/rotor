<?php
use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

class InitialMigration extends AbstractMigration
{
    public function up()
    {
        // Automatically created phinx migration commands for tables from database rotorcms

        // Migration for table admlog
        $table = $this->table('admlog');
        $table
            ->addColumn('user', 'string', ['limit' => 20])
            ->addColumn('request', 'string', ['limit' => 255, 'default' => ''])
            ->addColumn('referer', 'string', ['limit' => 255, 'default' => ''])
            ->addColumn('ip', 'string', ['limit' => 20, 'default' => ''])
            ->addColumn('brow', 'string', ['limit' => 25, 'default' => ''])
            ->addColumn('time', 'integer', ['signed' => false, 'default' => '0'])
            ->create();


        // Migration for table antimat
        $table = $this->table('antimat');
        $table
            ->addColumn('string', 'string', ['limit' => 100])
            ->create();


        // Migration for table ban
        $table = $this->table('ban');
        $table
            ->addColumn('ip', 'string', ['limit' => 15])
            ->addColumn('user', 'string', ['limit' => 20, 'default' => ''])
            ->addColumn('time', 'integer', ['signed' => false, 'default' => '0'])
            ->addIndex(['ip'], ['unique' => true])
            ->create();


        // Migration for table banhist
        $table = $this->table('banhist');
        $table
            ->addColumn('user', 'string', ['limit' => 20])
            ->addColumn('send', 'string', ['limit' => 20])
            ->addColumn('type', 'integer', ['limit' => MysqlAdapter::INT_TINY, 'signed' => false, 'default' => '0'])
            ->addColumn('reason', '[longtext]', ['limit' => MysqlAdapter::TEXT_LONG, 'null' => true])
            ->addColumn('term', 'integer', ['signed' => false, 'default' => '0'])
            ->addColumn('time', 'integer', ['signed' => false, 'default' => '0'])
            ->addIndex(['time'], )
            ->addIndex(['user'], )
            ->create();


        // Migration for table bank
        $table = $this->table('bank');
        $table
            ->addColumn('user', 'string', ['limit' => 20])
            ->addColumn('sum', 'integer', ['signed' => false, 'default' => '0'])
            ->addColumn('oper', 'integer', ['signed' => false, 'default' => '0'])
            ->addColumn('time', 'integer', ['signed' => false, 'default' => '0'])
            ->addIndex(['user'], ['unique' => true])
            ->create();


        // Migration for table bank2
        $table = $this->table('bank2');
        $table
            ->addColumn('user', 'string', ['limit' => 20])
            ->addColumn('sum', 'integer', ['signed' => false, 'default' => '0'])
            ->addColumn('oper', 'integer', ['signed' => false, 'default' => '0'])
            ->addColumn('time', 'integer', ['limit' => 11])
            ->addIndex(['user'], ['unique' => true])
            ->create();


        // Migration for table blacklist
        $table = $this->table('blacklist');
        $table
            ->addColumn('type', 'integer', ['limit' => MysqlAdapter::INT_TINY, 'signed' => false])
            ->addColumn('value', 'string', ['limit' => 100])
            ->addColumn('user', 'string', ['limit' => 20, 'default' => ''])
            ->addColumn('time', 'integer', ['signed' => false, 'default' => '0'])
            ->addIndex(['type'], )
            ->addIndex(['value'], )
            ->create();


        // Migration for table blacklist2
        $table = $this->table('blacklist2');
        $table
            ->addColumn('type', 'integer', ['limit' => MysqlAdapter::INT_TINY])
            ->addColumn('value', 'string', ['limit' => 100])
            ->addColumn('user', 'string', ['limit' => 20, 'default' => ''])
            ->addColumn('time', 'integer', ['limit' => 11])
            ->addIndex(['type'], )
            ->addIndex(['value'], )
            ->create();


        // Migration for table blogs
        $table = $this->table('blogs');
        $table
            ->addColumn('category_id', 'integer', ['limit' => MysqlAdapter::INT_SMALL, 'signed' => false, 'default' => '0'])
            ->addColumn('user', 'string', ['limit' => 20])
            ->addColumn('title', 'string', ['limit' => 50])
            ->addColumn('text', '[longtext]', ['limit' => MysqlAdapter::TEXT_LONG, 'null' => true])
            ->addColumn('tags', 'string', ['limit' => 100])
            ->addColumn('rating', 'integer', ['limit' => MysqlAdapter::INT_MEDIUM, 'default' => '0'])
            ->addColumn('visits', 'integer', ['signed' => false, 'default' => '0'])
            ->addColumn('comments', 'integer', ['limit' => MysqlAdapter::INT_MEDIUM, 'signed' => false, 'default' => '0'])
            ->addColumn('time', 'integer', ['signed' => false])
            ->addIndex(['category_id'], )
            ->addIndex(['time'], )
            ->addIndex(['user'], )
            ->create();


        // Migration for table bookmarks
        $table = $this->table('bookmarks');
        $table
            ->addColumn('user', 'string', ['limit' => 20])
            ->addColumn('topic_id', 'integer', ['limit' => MysqlAdapter::INT_MEDIUM, 'signed' => false])
            ->addColumn('forum_id', 'integer', ['limit' => MysqlAdapter::INT_SMALL, 'signed' => false])
            ->addColumn('posts', 'integer', ['limit' => MysqlAdapter::INT_MEDIUM, 'signed' => false])
            ->addIndex(['forum_id'], )
            ->addIndex(['topic_id'], )
            ->addIndex(['user'], )
            ->create();


        // Migration for table cats
        $table = $this->table('cats');
        $table
            ->addColumn('sort', 'integer', ['limit' => MysqlAdapter::INT_SMALL, 'signed' => false, 'default' => '0'])
            ->addColumn('parent', 'integer', ['limit' => MysqlAdapter::INT_SMALL, 'signed' => false, 'default' => '0'])
            ->addColumn('name', 'string', ['limit' => 100])
            ->addColumn('count', 'integer', ['limit' => MysqlAdapter::INT_MEDIUM, 'signed' => false, 'default' => '0'])
            ->addColumn('folder', 'string', ['limit' => 50, 'default' => ''])
            ->addColumn('closed', 'integer', ['limit' => MysqlAdapter::INT_TINY, 'signed' => false, 'default' => '0'])
            ->create();


        // Migration for table catsblog
        $table = $this->table('catsblog');
        $table
            ->addColumn('sort', 'integer', ['limit' => MysqlAdapter::INT_SMALL, 'signed' => false, 'default' => '0'])
            ->addColumn('name', 'string', ['limit' => 100])
            ->addColumn('count', 'integer', ['limit' => MysqlAdapter::INT_MEDIUM, 'signed' => false, 'default' => '0'])
            ->create();


        // Migration for table changemail
        $table = $this->table('changemail');
        $table
            ->addColumn('user', 'string', ['limit' => 20])
            ->addColumn('mail', 'string', ['limit' => 50])
            ->addColumn('hash', 'string', ['limit' => 25])
            ->addColumn('time', 'integer', ['signed' => false])
            ->create();


        // Migration for table chat
        $table = $this->table('chat');
        $table
            ->addColumn('user', 'string', ['limit' => 20])
            ->addColumn('text', '[longtext]', ['limit' => MysqlAdapter::TEXT_LONG, 'null' => true])
            ->addColumn('ip', 'string', ['limit' => 20])
            ->addColumn('brow', 'string', ['limit' => 25])
            ->addColumn('time', 'integer', ['signed' => false])
            ->addColumn('edit', 'string', ['limit' => 20, 'default' => ''])
            ->addColumn('edit_time', 'integer', ['signed' => false, 'default' => '0'])
            ->addIndex(['time'], )
            ->create();


        // Migration for table comments
        $table = $this->table('comments');
        $table
            ->addColumn('user', 'string', ['limit' => 20])
            ->addColumn('relate_type', 'enum', ['values' => array('blog','event','down','news','offer','gallery')])
            ->addColumn('relate_category_id', 'integer', ['signed' => false])
            ->addColumn('relate_id', 'integer', ['signed' => false])
            ->addColumn('text', 'text', ['null' => true])
            ->addColumn('ip', 'string', ['limit' => 15])
            ->addColumn('brow', 'string', ['limit' => 25])
            ->addColumn('time', 'integer', ['signed' => false])
            ->addIndex(['relate_type', 'relate_id'], )
            ->addIndex(['time'], )
            ->create();


        // Migration for table contact
        $table = $this->table('contact');
        $table
            ->addColumn('user', 'string', ['limit' => 20])
            ->addColumn('name', 'string', ['limit' => 20])
            ->addColumn('text', '[longtext]', ['limit' => MysqlAdapter::TEXT_LONG, 'null' => true])
            ->addColumn('time', 'integer', ['signed' => false])
            ->addIndex(['time'], )
            ->addIndex(['user'], )
            ->create();


        // Migration for table counter
        $table = $this->table('counter');
        $table
            ->addColumn('hours', 'integer', ['limit' => MysqlAdapter::INT_MEDIUM, 'signed' => false])
            ->addColumn('days', 'integer', ['limit' => MysqlAdapter::INT_MEDIUM, 'signed' => false])
            ->addColumn('allhosts', 'integer', ['signed' => false])
            ->addColumn('allhits', 'integer', ['signed' => false])
            ->addColumn('dayhosts', 'integer', ['limit' => MysqlAdapter::INT_MEDIUM, 'signed' => false])
            ->addColumn('dayhits', 'integer', ['limit' => MysqlAdapter::INT_MEDIUM, 'signed' => false])
            ->addColumn('hosts24', 'integer', ['limit' => MysqlAdapter::INT_MEDIUM, 'signed' => false])
            ->addColumn('hits24', 'integer', ['limit' => MysqlAdapter::INT_MEDIUM, 'signed' => false])
            ->create();


        // Migration for table counter24
        $table = $this->table('counter24');
        $table
            ->addColumn('hour', 'integer', ['limit' => MysqlAdapter::INT_MEDIUM, 'signed' => false])
            ->addColumn('hosts', 'integer', ['limit' => MysqlAdapter::INT_MEDIUM, 'signed' => false])
            ->addColumn('hits', 'integer', ['limit' => MysqlAdapter::INT_MEDIUM, 'signed' => false])
            ->addIndex(['hour'], ['unique' => true])
            ->create();


        // Migration for table counter31
        $table = $this->table('counter31');
        $table
            ->addColumn('days', 'integer', ['limit' => MysqlAdapter::INT_MEDIUM, 'signed' => false])
            ->addColumn('hosts', 'integer', ['limit' => MysqlAdapter::INT_MEDIUM, 'signed' => false])
            ->addColumn('hits', 'integer', ['limit' => MysqlAdapter::INT_MEDIUM, 'signed' => false])
            ->addIndex(['days'], ['unique' => true])
            ->create();


        // Migration for table downs
        $table = $this->table('downs');
        $table
            ->addColumn('category_id', 'integer', ['limit' => MysqlAdapter::INT_SMALL, 'signed' => false, 'default' => '0'])
            ->addColumn('title', 'string', ['limit' => 100])
            ->addColumn('text', '[longtext]', ['limit' => MysqlAdapter::TEXT_LONG, 'null' => true])
            ->addColumn('link', 'string', ['limit' => 50])
            ->addColumn('user', 'string', ['limit' => 20])
            ->addColumn('author', 'string', ['limit' => 50])
            ->addColumn('site', 'string', ['limit' => 50, 'default' => ''])
            ->addColumn('screen', 'string', ['limit' => 50, 'default' => ''])
            ->addColumn('time', 'integer', ['signed' => false, 'default' => '0'])
            ->addColumn('comments', 'integer', ['limit' => MysqlAdapter::INT_MEDIUM, 'signed' => false, 'default' => '0'])
            ->addColumn('rating', 'integer', ['limit' => MysqlAdapter::INT_MEDIUM, 'signed' => false, 'default' => '0'])
            ->addColumn('rated', 'integer', ['limit' => MysqlAdapter::INT_MEDIUM, 'signed' => false, 'default' => '0'])
            ->addColumn('loads', 'integer', ['limit' => MysqlAdapter::INT_MEDIUM, 'signed' => false, 'default' => '0'])
            ->addColumn('last_load', 'integer', ['signed' => false, 'default' => '0'])
            ->addColumn('app', 'integer', ['limit' => MysqlAdapter::INT_TINY, 'signed' => false, 'default' => '0'])
            ->addColumn('notice', '[longtext]', ['limit' => MysqlAdapter::TEXT_LONG, 'null' => true])
            ->addColumn('active', 'integer', ['limit' => MysqlAdapter::INT_TINY, 'signed' => false, 'default' => '0'])
            ->addIndex(['category_id'], )
            ->addIndex(['time'], )
            ->addIndex(['text'], )
            ->addIndex(['title'], )
            ->create();


        // Migration for table error
        $table = $this->table('error');
        $table
            ->addColumn('num', 'integer', ['limit' => MysqlAdapter::INT_SMALL, 'signed' => false])
            ->addColumn('request', 'string', ['limit' => 255, 'default' => ''])
            ->addColumn('referer', 'string', ['limit' => 255, 'default' => ''])
            ->addColumn('username', 'string', ['limit' => 20, 'default' => ''])
            ->addColumn('ip', 'string', ['limit' => 20, 'default' => ''])
            ->addColumn('brow', 'string', ['limit' => 25, 'default' => ''])
            ->addColumn('time', 'integer', ['signed' => false, 'default' => '0'])
            ->addIndex(['num', 'time'], )
            ->create();


        // Migration for table events
        $table = $this->table('events');
        $table
            ->addColumn('title', 'string', ['limit' => 100])
            ->addColumn('text', '[longtext]', ['limit' => MysqlAdapter::TEXT_LONG, 'null' => true])
            ->addColumn('author', 'string', ['limit' => 20])
            ->addColumn('image', 'string', ['limit' => 30, 'default' => ''])
            ->addColumn('time', 'integer', ['signed' => false, 'default' => '0'])
            ->addColumn('comments', 'integer', ['limit' => MysqlAdapter::INT_MEDIUM, 'signed' => false, 'default' => '0'])
            ->addColumn('closed', 'integer', ['limit' => MysqlAdapter::INT_TINY, 'signed' => false, 'default' => '0'])
            ->addColumn('top', 'integer', ['limit' => MysqlAdapter::INT_TINY, 'signed' => false, 'default' => '0'])
            ->addIndex(['time'], )
            ->create();


        // Migration for table files_forum
        $table = $this->table('files_forum');
        $table
            ->addColumn('topic_id', 'integer', ['limit' => MysqlAdapter::INT_MEDIUM, 'signed' => false])
            ->addColumn('post_id', 'integer', ['signed' => false])
            ->addColumn('hash', 'string', ['limit' => 40])
            ->addColumn('name', 'string', ['limit' => 60])
            ->addColumn('size', 'integer', ['signed' => false])
            ->addColumn('user', 'string', ['limit' => 20])
            ->addColumn('time', 'integer', ['signed' => false, 'default' => '0'])
            ->addIndex(['topic_id'], )
            ->addIndex(['post_id'], )
            ->addIndex(['user'], )
            ->addIndex(['time'], )
            ->create();


        // Migration for table flood
        $table = $this->table('flood');
        $table
            ->addColumn('user', 'string', ['limit' => 20])
            ->addColumn('page', 'string', ['limit' => 30])
            ->addColumn('time', 'integer', ['signed' => false])
            ->addIndex(['user'], )
            ->create();


        // Migration for table forums
        $table = $this->table('forums');
        $table
            ->addColumn('sort', 'integer', ['limit' => MysqlAdapter::INT_SMALL, 'signed' => false, 'default' => '0'])
            ->addColumn('parent', 'integer', ['limit' => MysqlAdapter::INT_SMALL, 'signed' => false, 'default' => '0'])
            ->addColumn('title', 'string', ['limit' => 50])
            ->addColumn('desc', 'string', ['limit' => 100, 'default' => ''])
            ->addColumn('topics', 'integer', ['signed' => false, 'default' => '0'])
            ->addColumn('posts', 'integer', ['signed' => false, 'default' => '0'])
            ->addColumn('last_id', 'integer', ['signed' => false, 'default' => '0'])
            ->addColumn('last_themes', 'string', ['limit' => 50, 'default' => ''])
            ->addColumn('last_user', 'string', ['limit' => 20, 'default' => ''])
            ->addColumn('last_time', 'integer', ['signed' => false, 'default' => '0'])
            ->addColumn('closed', 'integer', ['limit' => MysqlAdapter::INT_TINY, 'signed' => false, 'default' => '0'])
            ->create();


        // Migration for table guest
        $table = $this->table('guest');
        $table
            ->addColumn('user', 'string', ['limit' => 20])
            ->addColumn('text', '[mediumtext]', ['limit' => MysqlAdapter::TEXT_MEDIUM, 'null' => true])
            ->addColumn('ip', 'string', ['limit' => 20])
            ->addColumn('brow', 'string', ['limit' => 25])
            ->addColumn('time', 'integer', ['signed' => false])
            ->addColumn('reply', '[mediumtext]', ['limit' => MysqlAdapter::TEXT_MEDIUM, 'null' => true])
            ->addColumn('edit', 'string', ['limit' => 20, 'default' => ''])
            ->addColumn('edit_time', 'integer', ['signed' => false, 'default' => '0'])
            ->addIndex(['time'], )
            ->create();


        // Migration for table ignoring
        $table = $this->table('ignoring');
        $table
            ->addColumn('user', 'string', ['limit' => 20])
            ->addColumn('name', 'string', ['limit' => 20])
            ->addColumn('text', '[mediumtext]', ['limit' => MysqlAdapter::TEXT_MEDIUM, 'null' => true])
            ->addColumn('time', 'integer', ['signed' => false])
            ->addIndex(['time'], )
            ->addIndex(['user'], )
            ->create();


        // Migration for table inbox
        $table = $this->table('inbox');
        $table
            ->addColumn('user', 'string', ['limit' => 20])
            ->addColumn('author', 'string', ['limit' => 20])
            ->addColumn('text', '[mediumtext]', ['limit' => MysqlAdapter::TEXT_MEDIUM, 'null' => true])
            ->addColumn('time', 'integer', ['signed' => false])
            ->addIndex(['time'], )
            ->addIndex(['user'], )
            ->create();


        // Migration for table input
        $table = $this->table('input');
        $table
            ->addColumn('input_id', 'integer', ['signed' => false])
            ->addColumn('input_mail', 'string', ['limit' => 50])
            ->addColumn('input_code', 'string', ['limit' => 25])
            ->addColumn('input_ip', 'string', ['limit' => 20])
            ->addColumn('input_brow', 'string', ['limit' => 25])
            ->addColumn('input_time', 'integer', ['signed' => false])
            ->addColumn('input_user', 'string', ['limit' => 20])
            ->addColumn('input_cms', 'integer', ['limit' => MysqlAdapter::INT_TINY, 'signed' => false])
            ->addIndex(['input_id'], ['unique' => true])
            ->create();


        // Migration for table invite
        $table = $this->table('invite');
        $table
            ->addColumn('hash', 'string', ['limit' => 15])
            ->addColumn('user', 'string', ['limit' => 20])
            ->addColumn('invited', 'string', ['limit' => 20, 'default' => ''])
            ->addColumn('used', 'integer', ['limit' => MysqlAdapter::INT_TINY, 'signed' => false, 'default' => '0'])
            ->addColumn('time', 'integer', ['signed' => false])
            ->addIndex(['user'], )
            ->addIndex(['used'], )
            ->addIndex(['time'], )
            ->create();


        // Migration for table loads
        $table = $this->table('loads');
        $table
            ->addColumn('down', 'integer', ['limit' => MysqlAdapter::INT_MEDIUM, 'signed' => false])
            ->addColumn('ip', 'string', ['limit' => 20])
            ->addColumn('time', 'integer', ['signed' => false])
            ->create();


        // Migration for table login
        $table = $this->table('login');
        $table
            ->addColumn('user', 'string', ['limit' => 20])
            ->addColumn('ip', 'string', ['limit' => 15])
            ->addColumn('brow', 'string', ['limit' => 25])
            ->addColumn('time', 'integer', ['signed' => false])
            ->addColumn('type', 'integer', ['limit' => MysqlAdapter::INT_TINY, 'signed' => false, 'default' => '0'])
            ->addIndex(['time'], )
            ->addIndex(['user'], )
            ->create();


        // Migration for table lotinfo
        $table = $this->table('lotinfo');
        $table
            ->addColumn('date', 'integer', ['limit' => MysqlAdapter::INT_SMALL, 'signed' => false])
            ->addColumn('sum', 'integer', ['signed' => false, 'default' => '0'])
            ->addColumn('newnum', 'integer', ['limit' => MysqlAdapter::INT_SMALL, 'signed' => false, 'default' => '0'])
            ->addColumn('oldnum', 'integer', ['limit' => MysqlAdapter::INT_SMALL, 'signed' => false, 'default' => '0'])
            ->addColumn('winners', 'string', ['limit' => 255, 'default' => ''])
            ->create();


        // Migration for table lotusers
        $table = $this->table('lotusers');
        $table
            ->addColumn('user', 'string', ['limit' => 20])
            ->addColumn('num', 'integer', ['limit' => MysqlAdapter::INT_SMALL, 'signed' => false, 'default' => '0'])
            ->addColumn('time', 'integer', ['signed' => false, 'default' => '0'])
            ->create();


        // Migration for table migrations
        $table = $this->table('migrations');
        $table
            ->addColumn('version', '[bigint]', ['limit' => MysqlAdapter::INT_BIG])
            ->addColumn('migration_name', 'string', ['limit' => 100, 'null' => true])
            ->addColumn('start_time', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('end_time', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('breakpoint', 'integer', ['limit' => MysqlAdapter::INT_TINY, 'default' => '0'])
            ->addIndex(['version'], ['unique' => true])
            ->create();


        // Migration for table news
        $table = $this->table('news');
        $table
            ->addColumn('title', 'string', ['limit' => 100])
            ->addColumn('text', '[mediumtext]', ['limit' => MysqlAdapter::TEXT_MEDIUM, 'null' => true])
            ->addColumn('author', 'string', ['limit' => 20])
            ->addColumn('image', 'string', ['limit' => 30, 'default' => ''])
            ->addColumn('time', 'integer', ['signed' => false, 'default' => '0'])
            ->addColumn('comments', 'integer', ['limit' => MysqlAdapter::INT_MEDIUM, 'signed' => false, 'default' => '0'])
            ->addColumn('closed', 'integer', ['limit' => MysqlAdapter::INT_TINY, 'signed' => false, 'default' => '0'])
            ->addColumn('top', 'integer', ['limit' => MysqlAdapter::INT_TINY, 'signed' => false, 'default' => '0'])
            ->addIndex(['time'], )
            ->create();


        // Migration for table note
        $table = $this->table('note');
        $table
            ->addColumn('user', 'string', ['limit' => 20])
            ->addColumn('text', '[mediumtext]', ['limit' => MysqlAdapter::TEXT_MEDIUM, 'null' => true])
            ->addColumn('edit', 'string', ['limit' => 20])
            ->addColumn('time', 'integer', ['signed' => false, 'default' => '0'])
            ->addIndex(['user'], ['unique' => true])
            ->create();


        // Migration for table notebook
        $table = $this->table('notebook');
        $table
            ->addColumn('user', 'string', ['limit' => 20])
            ->addColumn('text', '[mediumtext]', ['limit' => MysqlAdapter::TEXT_MEDIUM, 'null' => true])
            ->addColumn('time', 'integer', ['signed' => false, 'default' => '0'])
            ->addIndex(['user'], ['unique' => true])
            ->create();


        // Migration for table notice
        $table = $this->table('notice');
        $table
            ->addColumn('name', 'string', ['limit' => 100])
            ->addColumn('text', '[mediumtext]', ['limit' => MysqlAdapter::TEXT_MEDIUM, 'null' => true])
            ->addColumn('user', 'string', ['limit' => 20, 'default' => ''])
            ->addColumn('time', 'integer', ['signed' => false, 'default' => '0'])
            ->addColumn('protect', 'integer', ['limit' => MysqlAdapter::INT_TINY, 'signed' => false, 'default' => '0'])
            ->create();


        // Migration for table offers
        $table = $this->table('offers');
        $table
            ->addColumn('type', 'integer', ['limit' => MysqlAdapter::INT_TINY, 'signed' => false, 'default' => '0'])
            ->addColumn('title', 'string', ['limit' => 50])
            ->addColumn('text', '[mediumtext]', ['limit' => MysqlAdapter::TEXT_MEDIUM, 'null' => true])
            ->addColumn('user', 'string', ['limit' => 20])
            ->addColumn('votes', 'integer', ['limit' => MysqlAdapter::INT_MEDIUM, 'signed' => false, 'default' => '0'])
            ->addColumn('time', 'integer', ['signed' => false, 'default' => '0'])
            ->addColumn('status', 'integer', ['limit' => MysqlAdapter::INT_TINY, 'signed' => false, 'default' => '0'])
            ->addColumn('comments', 'integer', ['limit' => MysqlAdapter::INT_MEDIUM, 'signed' => false, 'default' => '0'])
            ->addColumn('closed', 'integer', ['limit' => MysqlAdapter::INT_TINY, 'signed' => false, 'default' => '0'])
            ->addColumn('text_reply', '[mediumtext]', ['limit' => MysqlAdapter::TEXT_MEDIUM, 'null' => true])
            ->addColumn('user_reply', 'string', ['limit' => 20, 'default' => ''])
            ->addColumn('time_reply', 'integer', ['signed' => false, 'default' => '0'])
            ->addIndex(['time'], )
            ->addIndex(['votes'], )
            ->create();


        // Migration for table online
        $table = $this->table('online');
        $table
            ->addColumn('ip', 'string', ['limit' => 15])
            ->addColumn('brow', 'string', ['limit' => 25])
            ->addColumn('time', 'integer', ['signed' => false])
            ->addColumn('user', 'string', ['limit' => 20, 'default' => ''])
            ->addIndex(['ip'], )
            ->addIndex(['time'], )
            ->addIndex(['user'], )
            ->create();


        // Migration for table outbox
        $table = $this->table('outbox');
        $table
            ->addColumn('user', 'string', ['limit' => 20])
            ->addColumn('author', 'string', ['limit' => 20])
            ->addColumn('text', '[mediumtext]', ['limit' => MysqlAdapter::TEXT_MEDIUM, 'null' => true])
            ->addColumn('time', 'integer', ['signed' => false])
            ->addIndex(['time'], )
            ->addIndex(['user'], )
            ->create();


        // Migration for table photo
        $table = $this->table('photo');
        $table
            ->addColumn('user', 'string', ['limit' => 20])
            ->addColumn('title', 'string', ['limit' => 50])
            ->addColumn('text', '[mediumtext]', ['limit' => MysqlAdapter::TEXT_MEDIUM, 'null' => true])
            ->addColumn('link', 'string', ['limit' => 30])
            ->addColumn('time', 'integer', ['signed' => false])
            ->addColumn('rating', 'integer', ['limit' => MysqlAdapter::INT_MEDIUM, 'default' => '0'])
            ->addColumn('closed', 'integer', ['limit' => MysqlAdapter::INT_TINY, 'signed' => false, 'default' => '0'])
            ->addColumn('comments', 'integer', ['signed' => false, 'default' => '0'])
            ->addIndex(['time'], )
            ->addIndex(['user'], )
            ->create();


        // Migration for table pollings
        $table = $this->table('pollings');
        $table
            ->addColumn('relate_type', 'string', ['limit' => 20])
            ->addColumn('relate_id', 'integer', ['signed' => false])
            ->addColumn('user', 'string', ['limit' => 20])
            ->addColumn('vote', 'integer', ['limit' => MysqlAdapter::INT_TINY, 'default' => '1'])
            ->addColumn('time', 'integer', ['signed' => false])
            ->addIndex(['relate_type', 'relate_id', 'user'], )
            ->create();


        // Migration for table posts
        $table = $this->table('posts');
        $table
            ->addColumn('forum_id', 'integer', ['limit' => MysqlAdapter::INT_SMALL, 'signed' => false])
            ->addColumn('topic_id', 'integer', ['limit' => MysqlAdapter::INT_MEDIUM, 'signed' => false])
            ->addColumn('user', 'string', ['limit' => 20])
            ->addColumn('text', '[mediumtext]', ['limit' => MysqlAdapter::TEXT_MEDIUM, 'null' => true])
            ->addColumn('rating', 'integer', ['limit' => MysqlAdapter::INT_SMALL, 'default' => '0'])
            ->addColumn('time', 'integer', ['signed' => false])
            ->addColumn('ip', 'string', ['limit' => 15])
            ->addColumn('brow', 'string', ['limit' => 25])
            ->addColumn('edit', 'string', ['limit' => 20, 'default' => ''])
            ->addColumn('edit_time', 'integer', ['signed' => false, 'default' => '0'])
            ->addIndex(['forum_id'], )
            ->addIndex(['topic_id', 'time'], )
            ->addIndex(['user'], )
            ->addIndex(['text'], )
            ->create();


        // Migration for table rating
        $table = $this->table('rating');
        $table
            ->addColumn('user', 'string', ['limit' => 20])
            ->addColumn('login', 'string', ['limit' => 20])
            ->addColumn('text', '[mediumtext]', ['limit' => MysqlAdapter::TEXT_MEDIUM, 'null' => true])
            ->addColumn('vote', 'integer', ['limit' => MysqlAdapter::INT_TINY, 'signed' => false, 'default' => '0'])
            ->addColumn('time', 'integer', ['signed' => false, 'default' => '0'])
            ->addIndex(['user'], )
            ->create();


        // Migration for table readblog
        $table = $this->table('readblog');
        $table
            ->addColumn('blog', 'integer', ['signed' => false])
            ->addColumn('ip', 'string', ['limit' => 20])
            ->addColumn('time', 'integer', ['signed' => false])
            ->create();


        // Migration for table rekuser
        $table = $this->table('rekuser');
        $table
            ->addColumn('site', 'string', ['limit' => 50])
            ->addColumn('name', 'string', ['limit' => 50])
            ->addColumn('color', 'string', ['limit' => 10, 'default' => ''])
            ->addColumn('bold', 'integer', ['limit' => MysqlAdapter::INT_TINY, 'signed' => false, 'default' => '0'])
            ->addColumn('user', 'string', ['limit' => 20])
            ->addColumn('time', 'integer', ['signed' => false])
            ->create();


        // Migration for table rules
        $table = $this->table('rules');
        $table
            ->addColumn('text', '[mediumtext]', ['limit' => MysqlAdapter::TEXT_MEDIUM, 'null' => true])
            ->addColumn('time', 'integer', ['signed' => false, 'default' => '0'])
            ->create();


        // Migration for table setting
        $table = $this->table('setting');
        $table
            ->addColumn('name', 'string', ['limit' => 25])
            ->addColumn('value', 'string', ['limit' => 255])
            ->addIndex(['name'], ['unique' => true])
            ->create();


        // Migration for table smiles
        $table = $this->table('smiles');
        $table
            ->addColumn('cats', 'integer', ['limit' => MysqlAdapter::INT_SMALL, 'signed' => false])
            ->addColumn('name', 'string', ['limit' => 100])
            ->addColumn('code', 'string', ['limit' => 20])
            ->addIndex(['cats'], )
            ->addIndex(['code'], )
            ->create();


        // Migration for table socials
        $table = $this->table('socials');
        $table
            ->addColumn('user', 'string', ['limit' => 128])
            ->addColumn('network', 'string', ['limit' => 255])
            ->addColumn('uid', 'string', ['limit' => 255])
            ->addIndex(['user'], )
            ->create();


        // Migration for table spam
        $table = $this->table('spam');
        $table
            ->addColumn('relate', 'integer', ['limit' => MysqlAdapter::INT_TINY, 'signed' => false])
            ->addColumn('idnum', 'integer', ['signed' => false])
            ->addColumn('user', 'string', ['limit' => 20])
            ->addColumn('login', 'string', ['limit' => 20])
            ->addColumn('text', '[mediumtext]', ['limit' => MysqlAdapter::TEXT_MEDIUM, 'null' => true])
            ->addColumn('time', 'integer', ['signed' => false])
            ->addColumn('addtime', 'integer', ['signed' => false])
            ->addColumn('link', 'string', ['limit' => 100])
            ->addIndex(['relate'], )
            ->addIndex(['time'], )
            ->create();


        // Migration for table status
        $table = $this->table('status');
        $table
            ->addColumn('topoint', 'integer', ['limit' => MysqlAdapter::INT_MEDIUM, 'signed' => false])
            ->addColumn('point', 'integer', ['limit' => MysqlAdapter::INT_MEDIUM, 'signed' => false])
            ->addColumn('name', 'string', ['limit' => 50])
            ->addColumn('color', 'string', ['limit' => 10, 'default' => ''])
            ->addIndex(['point'], )
            ->addIndex(['topoint'], )
            ->create();


        // Migration for table topics
        $table = $this->table('topics');
        $table
            ->addColumn('forum_id', 'integer', ['limit' => MysqlAdapter::INT_SMALL, 'signed' => false])
            ->addColumn('title', 'string', ['limit' => 50])
            ->addColumn('author', 'string', ['limit' => 20])
            ->addColumn('closed', 'integer', ['limit' => MysqlAdapter::INT_TINY, 'signed' => false, 'default' => '0'])
            ->addColumn('locked', 'integer', ['limit' => MysqlAdapter::INT_TINY, 'signed' => false, 'default' => '0'])
            ->addColumn('posts', 'integer', ['signed' => false, 'default' => '0'])
            ->addColumn('last_user', 'string', ['limit' => 20, 'default' => ''])
            ->addColumn('last_time', 'integer', ['signed' => false, 'default' => '0'])
            ->addColumn('moderators', 'string', ['limit' => 255, 'default' => ''])
            ->addColumn('note', 'string', ['limit' => 255, 'default' => ''])
            ->addIndex(['forum_id'], )
            ->addIndex(['last_time'], )
            ->addIndex(['locked'], )
            ->addIndex(['title'], )
            ->create();


        // Migration for table transfers
        $table = $this->table('transfers');
        $table
            ->addColumn('user', 'string', ['limit' => 20])
            ->addColumn('login', 'string', ['limit' => 20])
            ->addColumn('text', '[mediumtext]', ['limit' => MysqlAdapter::TEXT_MEDIUM, 'null' => true])
            ->addColumn('summ', 'integer', ['signed' => false, 'default' => '0'])
            ->addColumn('time', 'integer', ['signed' => false, 'default' => '0'])
            ->addIndex(['login'], )
            ->addIndex(['time'], )
            ->addIndex(['user'], )
            ->create();


        // Migration for table trash
        $table = $this->table('trash');
        $table
            ->addColumn('user', 'string', ['limit' => 20])
            ->addColumn('author', 'string', ['limit' => 20])
            ->addColumn('text', '[mediumtext]', ['limit' => MysqlAdapter::TEXT_MEDIUM, 'null' => true])
            ->addColumn('time', 'integer', ['signed' => false])
            ->addColumn('del', 'integer', ['signed' => false])
            ->addIndex(['time'], )
            ->addIndex(['user'], )
            ->create();


        // Migration for table users
        $table = $this->table('users');
        $table
            ->addColumn('login', 'string', ['limit' => 20])
            ->addColumn('password', 'string', ['limit' => 128])
            ->addColumn('email', 'string', ['limit' => 50])
            ->addColumn('joined', 'integer', ['signed' => false])
            ->addColumn('level', 'integer', ['limit' => MysqlAdapter::INT_SMALL, 'signed' => false, 'default' => '107'])
            ->addColumn('nickname', 'string', ['limit' => 20, 'default' => ''])
            ->addColumn('name', 'string', ['limit' => 20, 'default' => ''])
            ->addColumn('country', 'string', ['limit' => 30, 'default' => ''])
            ->addColumn('city', 'string', ['limit' => 50, 'default' => ''])
            ->addColumn('info', '[mediumtext]', ['limit' => MysqlAdapter::TEXT_MEDIUM, 'null' => true])
            ->addColumn('site', 'string', ['limit' => 50, 'default' => ''])
            ->addColumn('icq', 'string', ['limit' => 10, 'default' => ''])
            ->addColumn('skype', 'string', ['limit' => 32, 'default' => ''])
            ->addColumn('gender', 'integer', ['limit' => MysqlAdapter::INT_TINY, 'signed' => false, 'default' => '0'])
            ->addColumn('birthday', 'string', ['limit' => 10, 'default' => ''])
            ->addColumn('visits', 'integer', ['signed' => false, 'default' => '0'])
            ->addColumn('newprivat', 'integer', ['limit' => MysqlAdapter::INT_SMALL, 'signed' => false, 'default' => '0'])
            ->addColumn('newwall', 'integer', ['limit' => MysqlAdapter::INT_SMALL, 'signed' => false, 'default' => '0'])
            ->addColumn('allforum', 'integer', ['signed' => false, 'default' => '0'])
            ->addColumn('allguest', 'integer', ['signed' => false, 'default' => '0'])
            ->addColumn('allcomments', 'integer', ['signed' => false, 'default' => '0'])
            ->addColumn('themes', 'string', ['limit' => 20, 'default' => ''])
            ->addColumn('timezone', 'string', ['limit' => 3, 'default' => '0'])
            ->addColumn('point', 'integer', ['signed' => false, 'default' => '0'])
            ->addColumn('money', 'integer', ['signed' => false, 'default' => '0'])
            ->addColumn('ban', 'integer', ['limit' => MysqlAdapter::INT_TINY, 'signed' => false, 'default' => '0'])
            ->addColumn('timeban', 'integer', ['signed' => false, 'default' => '0'])
            ->addColumn('timelastban', 'integer', ['signed' => false, 'default' => '0'])
            ->addColumn('reasonban', '[mediumtext]', ['limit' => MysqlAdapter::TEXT_MEDIUM, 'null' => true])
            ->addColumn('loginsendban', 'string', ['limit' => 20, 'default' => ''])
            ->addColumn('totalban', 'integer', ['limit' => MysqlAdapter::INT_TINY, 'signed' => false, 'default' => '0'])
            ->addColumn('explainban', 'integer', ['limit' => MysqlAdapter::INT_TINY, 'signed' => false, 'default' => '0'])
            ->addColumn('status', 'string', ['limit' => 50, 'default' => ''])
            ->addColumn('avatar', 'string', ['limit' => 50, 'default' => ''])
            ->addColumn('picture', 'string', ['limit' => 50, 'default' => ''])
            ->addColumn('rating', 'integer', ['limit' => MysqlAdapter::INT_MEDIUM, 'default' => '0'])
            ->addColumn('posrating', 'integer', ['limit' => MysqlAdapter::INT_MEDIUM, 'signed' => false, 'default' => '0'])
            ->addColumn('negrating', 'integer', ['limit' => MysqlAdapter::INT_MEDIUM, 'signed' => false, 'default' => '0'])
            ->addColumn('keypasswd', 'string', ['limit' => 20, 'default' => ''])
            ->addColumn('timepasswd', 'integer', ['signed' => false, 'default' => '0'])
            ->addColumn('timelastlogin', 'integer', ['signed' => false, 'default' => '0'])
            ->addColumn('timebonus', 'integer', ['limit' => 11, 'default' => '0'])
            ->addColumn('sendprivatmail', 'integer', ['limit' => MysqlAdapter::INT_TINY, 'signed' => false, 'default' => '0'])
            ->addColumn('confirmreg', 'integer', ['limit' => MysqlAdapter::INT_TINY, 'signed' => false, 'default' => '0'])
            ->addColumn('confirmregkey', 'string', ['limit' => 30, 'default' => ''])
            ->addColumn('secquest', 'string', ['limit' => 50, 'default' => ''])
            ->addColumn('secanswer', 'string', ['limit' => 40, 'default' => ''])
            ->addColumn('timenickname', 'integer', ['signed' => false, 'default' => '0'])
            ->addColumn('ipbinding', 'integer', ['limit' => MysqlAdapter::INT_TINY, 'signed' => false, 'default' => '0'])
            ->addColumn('newchat', 'integer', ['signed' => false, 'default' => '0'])
            ->addColumn('privacy', 'integer', ['limit' => MysqlAdapter::INT_TINY, 'signed' => false, 'default' => '0'])
            ->addColumn('apikey', 'string', ['limit' => 32, 'default' => ''])
            ->addColumn('subscribe', 'string', ['limit' => 32, 'default' => ''])
            ->addColumn('sumcredit', 'integer', ['signed' => false, 'default' => '0'])
            ->addColumn('timecredit', 'integer', ['signed' => false, 'default' => '0'])
            ->addIndex(['email'], ['unique' => true])
            ->addIndex(['login'], ['unique' => true])
            ->addIndex(['level'], )
            ->addIndex(['nickname'], )
            ->addIndex(['themes'], )
            ->addIndex(['point'], )
            ->addIndex(['money'], )
            ->addIndex(['rating'], )
            ->create();


        // Migration for table visit
        $table = $this->table('visit');
        $table
            ->addColumn('user', 'string', ['limit' => 20])
            ->addColumn('self', 'string', ['limit' => 100, 'default' => ''])
            ->addColumn('page', 'string', ['limit' => 100, 'default' => ''])
            ->addColumn('ip', 'string', ['limit' => 15, 'default' => ''])
            ->addColumn('count', 'integer', ['signed' => false, 'default' => '0'])
            ->addColumn('nowtime', 'integer', ['signed' => false, 'default' => '0'])
            ->addIndex(['user'], ['unique' => true])
            ->addIndex(['nowtime'], )
            ->create();


        // Migration for table vote
        $table = $this->table('vote');
        $table
            ->addColumn('title', 'string', ['limit' => 100])
            ->addColumn('count', 'integer', ['limit' => MysqlAdapter::INT_SMALL, 'signed' => false, 'default' => '0'])
            ->addColumn('closed', 'integer', ['limit' => MysqlAdapter::INT_TINY, 'signed' => false, 'default' => '0'])
            ->addColumn('time', 'integer', ['signed' => false, 'default' => '0'])
            ->create();


        // Migration for table voteanswer
        $table = $this->table('voteanswer');
        $table
            ->addColumn('vote_id', 'integer', ['limit' => MysqlAdapter::INT_SMALL, 'signed' => false])
            ->addColumn('option', 'string', ['limit' => 50])
            ->addColumn('result', 'integer', ['limit' => MysqlAdapter::INT_SMALL, 'signed' => false, 'default' => '0'])
            ->create();


        // Migration for table votepoll
        $table = $this->table('votepoll');
        $table
            ->addColumn('vote_id', 'integer', ['limit' => MysqlAdapter::INT_SMALL, 'signed' => false])
            ->addColumn('user', 'string', ['limit' => 20])
            ->addColumn('time', 'integer', ['signed' => false, 'default' => '0'])
            ->create();


        // Migration for table wall
        $table = $this->table('wall');
        $table
            ->addColumn('user', 'string', ['limit' => 20])
            ->addColumn('login', 'string', ['limit' => 20])
            ->addColumn('text', '[mediumtext]', ['limit' => MysqlAdapter::TEXT_MEDIUM, 'null' => true])
            ->addColumn('time', 'integer', ['signed' => false])
            ->addIndex(['user'], )
            ->create();


    }
}
