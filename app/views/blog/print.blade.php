@extends('layout_simple')

@section('title')
    {{ $blog['title'] }} - @parent
@stop

@section('content')

    <h1><?=$blog['title']?></h1>

    <?=App::bbCode($blog['text'])?>

    <br /><br />

    URL: <a href="<?= App::setting('home') ?>/article/<?=$blog['id']?>"><?= App::setting('home')?>/article/<?=$blog['id']?></a>
@stop
