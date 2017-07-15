@extends('layout')

@section('title')
    Облако тегов - @parent
@stop

@section('content')
    <h1>Облако тегов</h1>
    <div style="text-align:center">
        <?php foreach ($tags as $key => $val): ?>

            <?php $fontsize = ($min != $max) ? round((($val - $min) / ($max - $min)) * 110 + 100) : 100; ?>

            <a href="/blog/tags/<?=urlencode($key)?>"><span style="font-size:<?=$fontsize?>%"><?=$key?></span></a>
        <?php endforeach; ?>
    </div><br />

    <?php App::view('includes/back', ['link' => '/blog', 'title' => 'К блогам']); ?>
@stop
