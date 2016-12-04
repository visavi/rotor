@extends('layout_rss')

@section('title', 'Темы форума - '.App::setting('home').' - @parent')

@section('content')

    <?php foreach ($topics as $topic): ?>
        <?php $topic['text'] = App::bbCode($topic['text']); ?>
        <?php $topic['text'] = str_replace('/upload/smiles', $config['home'].'/upload/smiles', $topic['text']); ?>
        <?php $topic['text'] = htmlspecialchars($topic['text']); ?>
        <?php $topic['title'] = htmlspecialchars($topic['title']); ?>

        <item>
            <title><?= $topic['title'] ?></title>
            <link><?= App::setting('home') ?>/topic/<?= $topic['topic_id'] ?></link>
            <description><?=$topic['text']?> </description>
            <author><?=nickname($topic['user'])?></author>
            <pubDate><?=date("r", $topic['time'])?></pubDate>
            <category>Темы</category>
            <guid><?= App::setting('home') ?>/topic/<?=$topic['topic_id']?></guid>
        </item>
    <?php endforeach; ?>
@stop
