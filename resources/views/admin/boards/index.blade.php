@extends('layout')

@section('title')
    {{ trans('boards.title') }}
@stop

@section('header')
    @if ($board)
        <h1>{{ $board->name }} <small>({{ trans('boards.title') }}: {{ $board->count_items }})</small></h1>
    @else
        <h1>{{ trans('boards.title') }}</h1>
    @endif
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ trans('main.panel') }}</a></li>

            @if ($board)
                <li class="breadcrumb-item"><a href="/admin/boards">{{ trans('boards.title') }}</a></li>

                @if ($board->parent->id)
                    <li class="breadcrumb-item"><a href="/admin/boards/{{ $board->parent->id }}">{{ $board->parent->name }}</a></li>
                @endif
                <li class="breadcrumb-item active">{{ $board->name }}</li>

                @if (isAdmin())
                    <li class="breadcrumb-item"><a href="/boards/{{ $board->id  }}?page={{ $page->current }}">{{ trans('main.review') }}</a></li>
                @endif
            @else
                <li class="breadcrumb-item active">{{ trans('boards.title') }}</li>

                @if (isAdmin())
                    <li class="breadcrumb-item"><a href="/boards?page={{ $page->current }}">{{ trans('main.review') }}</a></li>
                @endif
            @endif
        </ol>
    </nav>
@stop

@section('content')
    @if ($boards->isNotEmpty())
        <div class="row mb-3">
            @foreach ($boards->chunk(3) as $chunk)
                @foreach ($chunk as $board)
                    <div class="col-md-3 col-6">
                        <a href="/admin/boards/{{ $board->id }}">{{ $board->name }}</a> {{ $board->count_items }}
                    </div>
                @endforeach
            @endforeach
        </div>
    @endif

    @if ($items->isNotEmpty())
        @foreach ($items as $item)
            <div class="row mb-3">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <a href="/items/{{ $item->id }}">{!! $item->getFirstImage() !!}</a>
                                </div>
                                <div class="col-md-7">

                                    <div class="float-right">
                                        <a href="/admin/items/edit/{{ $item->id }}" data-toggle="tooltip" title="{{ trans('main.edit') }}"><i class="fa fa-pencil-alt"></i></a>
                                        <a href="/admin/items/delete/{{ $item->id }}?token={{ $_SESSION['token'] }}" onclick="return confirm('{{ trans('boards.confirm_delete') }}')" data-toggle="tooltip" title="{{ trans('main.delete') }}"><i class="fa fa-times"></i></a>
                                    </div>

                                    <h5><a href="/items/{{ $item->id }}">{{ $item->title }}</a></h5>
                                    <small><i class="fas fa-angle-right"></i> <a href="/boards/{{ $item->category->id }}">{{ $item->category->name }}</a></small>
                                    <div class="message">{!! $item->cutText() !!}</div>
                                    <p><i class="fa fa-user-circle"></i> {!! $item->user->getProfile() !!} / {{ dateFixed($item->created_at) }}</p>
                                </div>

                                <div class="col-md-2">
                                    @if ($item->price)
                                        <button type="button" class="btn btn-info">{{ $item->price }} {{ setting('currency') }}</button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach

        {!! pagination($page) !!}
    @else
        {!! showError(trans('boards.empty_items')) !!}
    @endif

    @if (isAdmin('boss'))
        <i class="far fa-list-alt"></i> <a href="/admin/boards/categories">{{ trans('boards.categories') }}</a><br>
        <i class="fa fa-sync"></i> <a href="/admin/boards/restatement?token={{ $_SESSION['token'] }}">{{ trans('main.recount') }}</a><br>
    @endif
@stop
