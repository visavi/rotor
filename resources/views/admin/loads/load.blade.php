@extends('layout')

@section('title')
    {{ $category->name }} ({{ trans('main.page_num', ['page' => $page->current]) }})
@stop

@section('header')
    @if (! $category->closed && getUser())
        <div class="float-right">
            <a class="btn btn-success" href="/downs/create?cid={{ $category->id }}">{{ trans('loads.create_down') }}</a>
        </div><br>
    @endif

    <h1>{{ $category->name }}</h1>
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ trans('index.panel') }}</a></li>
            <li class="breadcrumb-item"><a href="/admin/loads">{{ trans('index.loads') }}</a></li>

            @if ($category->parent->id)
                <li class="breadcrumb-item"><a href="/admin/loads/{{ $category->parent->id }}">{{ $category->parent->name }}</a></li>
            @endif

            <li class="breadcrumb-item active">{{ $category->name }}</li>

            @if (isAdmin('admin'))
                <li class="breadcrumb-item"><a href="/loads/{{ $category->id }}?page={{ $page->current }}">{{ trans('main.review') }}</a></li>
            @endif
        </ol>
    </nav>
@stop

@section('content')
    Сортировать:

    <?php $active = ($order === 'created_at') ? 'success' : 'light'; ?>
    <a href="/admin/loads/{{ $category->id }}?sort=time" class="badge badge-{{ $active }}">{{ trans('main.date') }}</a>

    <?php $active = ($order === 'loads') ? 'success' : 'light'; ?>
    <a href="/admin/loads/{{ $category->id }}?sort=loads" class="badge badge-{{ $active }}">{{ trans('main.downloads') }}</a>

    <?php $active = ($order === 'rated') ? 'success' : 'light'; ?>
    <a href="/admin/loads/{{ $category->id }}?sort=rated" class="badge badge-{{ $active }}">{{ trans('main.rating') }}</a>

    <?php $active = ($order === 'count_comments') ? 'success' : 'light'; ?>
    <a href="/admin/loads/{{ $category->id }}?sort=comments" class="badge badge-{{ $active }}">{{ trans('main.comments') }}</a>
    <hr>

    @if ($page->current === 1 && $category->children->isNotEmpty())
        <div class="act">
            @foreach ($category->children as $child)
                <div class="b">
                    <i class="fa fa-folder-open"></i>
                    <b><a href="/admin/loads/{{ $child->id }}">{{ $child->name }}</a></b> ({{ $child->count_downs }})</div>
            @endforeach
        </div>
        <hr>
    @endif

    @if ($downs->isNotEmpty())
        @foreach ($downs as $data)
            <?php $rating = $data->rated ? round($data->rating / $data->rated, 1) : 0; ?>
            <div class="b">
                <i class="fa fa-file"></i>
                <b><a href="/downs/{{ $data->id }}">{{ $data->title }}</a></b> ({{ $data->count_comments }})


                <div class="float-right">
                    <a href="/admin/downs/edit/{{ $data->id }}" title="{{ trans('main.edit') }}"><i class="fa fa-pencil-alt"></i></a>

                    @if (isAdmin('boss'))
                        <a href="/admin/downs/delete/{{ $data->id }}?token={{ $_SESSION['token'] }}" onclick="return confirm('{{ trans('loads.confirm_delete_down') }}')"><i class="fa fa-times"></i></a>
                    @endif
                </div>
            </div>

            <div>
                {{ trans('main.rating') }}: {{ $rating }}<br>
                {{ trans('main.downloads') }}: {{ $data->loads }}<br>
                <a href="/downs/comments/{{ $data->id }}">{{ trans('main.comments') }}</a> ({{ $data->count_comments }})
                <a href="/downs/end/{{ $data->id }}">&raquo;</a>
            </div>
        @endforeach

        {!! pagination($page) !!}
    @else
        @if (! $category->closed)
            {!! showError(trans('loads.empty_downs')) !!}
        @endif
    @endif

    @if ($category->closed)
        {!! showError(trans('loads.closed_load')) !!}
    @endif
@stop
