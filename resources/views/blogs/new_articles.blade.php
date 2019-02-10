@extends('layout')

@section('title')
    {{ trans('blogs.blogs') }} - Новые статьи ({{ trans('common.page_num', ['page' => $page->current]) }})
@stop

@section('header')
    <h1>Новые статьи</h1>
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/blogs">{{ trans('blogs.blogs') }}</a></li>
            <li class="breadcrumb-item active">Новые статьи</li>
        </ol>
    </nav>
@stop

@section('content')
    @if ($blogs->isNotEmpty())
        @foreach ($blogs as $data)
            <div class="b">
                <i class="fa fa-pencil-alt"></i>
                <b><a href="/articles/{{ $data->id }}">{{ $data->title }}</a></b> ({!! formatNum($data->rating) !!})
            </div>

            <div>
                Категория: <a href="/blogs/{{ $data->category_id }}">{{ $data->category->name }}</a><br>
                Просмотров: {{ $data->visits }}<br>
                Добавил: {!! $data->user->getProfile() !!}  ({{  dateFixed($data->created_at) }})
            </div>
        @endforeach

        {!! pagination($page) !!}
    @else
        {!! showError('Опубликованных статей еще нет!') !!}
    @endif
@stop
