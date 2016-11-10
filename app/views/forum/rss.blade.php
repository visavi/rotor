@extends('layout_rss')

@section('title', 'Сообщения - '.$topic['title'].' - @parent')

@section('content')

    <?php foreach ($posts as $data): ?>
        <?php $data['text'] = App::bbCode($data['text']); ?>
        <?php $data['text'] = str_replace('/images/smiles', $config['home'].'/images/smiles', $data['text']); ?>
        <?php $data['text'] = htmlspecialchars($data['text']); ?>

        <item>
            <title><?= $topic['title'] ?></title>
            <link><?= App::setting('home') ?>/topic/<?= $topic['id'] ?></link>
            <description><?=$data['text']?> </description>
            <author><?=nickname($data['user'])?></author>
            <pubDate><?=date("r", $data['time'])?></pubDate>
            <category>Сообщения</category>
            <guid><?= App::setting('home') ?>/topic/<?=$topic['id']?>/<?=$data['id']?></guid>
        </item>
    <?php endforeach; ?>
@stop
