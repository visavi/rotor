<?php

use Phinx\Migration\AbstractMigration;

class FillCommentsTable extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $comments = $this->fetchAll('SELECT * from commblog');
        $rows = [];
        foreach($comments as $comment) {
            $rows[] = [
                'user' => $comment['author'],
                'relate_type' => 'blog',
                'relate_category_id' => $comment['cats'],
                'relate_id' => $comment['blog'],
                'text' => $comment['text'],
                'ip' => $comment['ip'],
                'brow' => $comment['brow'],
                'time' => $comment['time'],
            ];
        }
        if ($rows) {
            $this->insert('comments', $rows);
        }

        $comments = $this->fetchAll('SELECT * from commevents');
        $rows = [];
        foreach($comments as $comment) {
            $rows[] = [
                'user' => $comment['author'],
                'relate_type' => 'event',
                'relate_category_id' => 0,
                'relate_id' => $comment['event_id'],
                'text' => $comment['text'],
                'ip' => $comment['ip'],
                'brow' => $comment['brow'],
                'time' => $comment['time'],
            ];
        }
        if ($rows) {
            $this->insert('comments', $rows);
        }

        $comments = $this->fetchAll('SELECT * from commload');
        $rows = [];
        foreach($comments as $comment) {
            $rows[] = [
                'user' => $comment['author'],
                'relate_type' => 'down',
                'relate_category_id' => $comment['cats'],
                'relate_id' => $comment['down'],
                'text' => $comment['text'],
                'ip' => $comment['ip'],
                'brow' => $comment['brow'],
                'time' => $comment['time'],
            ];
        }
        if ($rows) {
            $this->insert('comments', $rows);
        }

        $comments = $this->fetchAll('SELECT * from commnews');
        $rows = [];
        foreach($comments as $comment) {
            $rows[] = [
                'user' => $comment['author'],
                'relate_type' => 'news',
                'relate_category_id' => 0,
                'relate_id' => $comment['news_id'],
                'text' => $comment['text'],
                'ip' => $comment['ip'],
                'brow' => $comment['brow'],
                'time' => $comment['time'],
            ];
        }
        if ($rows) {
            $this->insert('comments', $rows);
        }

        $comments = $this->fetchAll('SELECT * from commoffers');
        $rows = [];
        foreach($comments as $comment) {
            $rows[] = [
                'user' => $comment['user'],
                'relate_type' => 'offer',
                'relate_category_id' => 0,
                'relate_id' => $comment['offers'],
                'text' => $comment['text'],
                'ip' => $comment['ip'],
                'brow' => $comment['brow'],
                'time' => $comment['time'],
            ];
        }
        if ($rows) {
            $this->insert('comments', $rows);
        }

        $comments = $this->fetchAll('SELECT * from commphoto');
        $rows = [];
        foreach($comments as $comment) {
            $rows[] = [
                'user' => $comment['user'],
                'relate_type' => 'gallery',
                'relate_category_id' => 0,
                'relate_id' => $comment['gid'],
                'text' => $comment['text'],
                'ip' => $comment['ip'],
                'brow' => $comment['brow'],
                'time' => $comment['time'],
            ];
        }
        if ($rows) {
            $this->insert('comments', $rows);
        }
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->execute("TRUNCATE comments");
    }
}
