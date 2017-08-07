@extends('layout_simple')

@section('title')
    {{ $blog['title'] }} - @parent
@stop

@section('content')

    <h1><?=$blog['title']?></h1>

    <?=App::bbCode($blog['text'])?>

    <br /><br />

    URL: <a href="<?= Setting::get('home') ?>/article/<?=$blog['id']?>"><?= Setting::get('home')?>/article/<?=$blog['id']?></a>
@stop
