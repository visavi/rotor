@extends('layout_simple')

@section('title')
    {{ $topic['title'] }} - @parent
@stop

@section('content')

    <h1><?=$topic['title']?></h1>

    <?php foreach ($posts as $key => $data): ?>

        <?=($key + 1)?>. <b><?= $data->getUser()->login ?></b> (<?=date_fixed($data['created_at'])?>)<br />
        <?=App::bbCode($data['text'])?>
        <br /><br />

    <?php endforeach; ?>

    URL: <a href="<?=$config['home']?>/topic/<?=$topic['id']?>"><?=$config['home']?>/topic/<?=$topic['id']?></a>
@stop
