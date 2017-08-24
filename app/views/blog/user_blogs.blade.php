@extends('layout')

@section('title')
    Статьи пользователей - @parent
@stop

@section('content')

    <h1>Статьи пользователей</h1>

    @if ($blogs->isNotEmpty())
        @foreach ($blogs as $data)
            <i class="fa fa-pencil"></i>
            <b><a href="/blog/active/articles?user={{ $data->login }}">{{ $data->login }}</a></b> ({{$data['cnt'] }} cтатей / {{ $data->comments }} комм.)<br>
        @endforeach

        {{ App::pagination($page) }}

        Всего пользователей: <b>{{  $page['total'] }}</b><br><br>
    @else
        {{ show_error('Статей еще нет!') }}
    @endif

    <?php App::view('includes/back', ['link' => '/blog', 'title' => 'К блогам']); ?>
@stop
