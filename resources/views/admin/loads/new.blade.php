@extends('layout')

@section('title')
    Новые публикации (Стр. {{ $page->current }})
@stop

@section('header')
    <h1>Новые публикации</h1>
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ trans('common.panel') }}</a></li>
            <li class="breadcrumb-item"><a href="/admin/loads">Загрузки</a></li>
            <li class="breadcrumb-item active">Новые публикации</li>
        </ol>
    </nav>
@stop

@section('content')
    @if ($downs->isNotEmpty())
        @foreach ($downs as $data)
            <div class="b">
                <i class="fa fa-file"></i>
                <b><a href="/downs/{{ $data->id }}">{{ $data->title }}</a></b> ({{ $data->count_comments }})


                <div class="float-right">
                    <a href="/admin/downs/edit/{{ $data->id }}" title="Редактировать"><i class="fa fa-pencil-alt"></i></a>

                    @if (isAdmin('boss'))
                        <a href="/admin/downs/delete/{{ $data->id }}?token={{ $_SESSION['token'] }}" onclick="return confirm('Вы уверены что хотите удалить данную загрузку?')"><i class="fa fa-times"></i></a>
                    @endif
                </div>
            </div>
                Категория: <a href="/admin/loads/{{ $data->category->id }}">{{ $data->category->name }}</a><br>
                Файлов/Картинок: {{ $data->getFiles()->count() }}/{{ $data->getImages()->count() }}<br>
                Автор: {!! $data->user->getProfile() !!} ({{ dateFixed($data->created_at) }})
            <div>

            </div>
        @endforeach

        {!! pagination($page) !!}
    @else
        {!! showError('Новых файлов еще нет!') !!}
    @endif
@stop
