@extends('layout')

@section('title')
    {{ trans('boards.categories') }}
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ trans('index.panel') }}</a></li>
            <li class="breadcrumb-item"><a href="/admin/boards">{{ trans('boards.title') }}</a></li>
            <li class="breadcrumb-item active">{{ trans('boards.categories') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    @foreach ($boards as $board)

        <div class="b">
            <i class="fa fa-folder-open"></i> <b><a href="/admin/board/{{ $board->id }}">{{ $board->name }}</a></b>

            ({{ $board->count_items }})

            <div class="float-right">
                <a href="/admin/boards/edit/{{ $board->id }}"><i class="fa fa-pencil-alt"></i></a>
                <a href="/admin/boards/delete/{{ $board->id }}?token={{ $_SESSION['token'] }}" onclick="return confirm('{{ trans('boards.confirm_delete_category') }}')"><i class="fa fa-times"></i></a>
            </div>
        </div>

        <div>
            @if ($board->children->isNotEmpty())
                @foreach ($board->children as $child)
                    <i class="fa fa-angle-right"></i> <b><a href="/admin/boards/{{ $child->id }}">{{ $child->name }}</a></b>

                    ({{ $child->count_items }})

                    <a href="/admin/boards/edit/{{ $child->id }}"><i class="fa fa-pencil-alt"></i></a>
                    <a href="/admin/boards/delete/{{ $child->id }}?token={{ $_SESSION['token'] }}" onclick="return confirm('{{ trans('boards.confirm_delete_category') }}')"><i class="fa fa-times"></i></a>
                    <br/>
                @endforeach
            @endif
        </div>
    @endforeach

    <div class="form my-3">
        <form action="/admin/boards/create" method="post">
            @csrf
            <div class="form-inline">
                <div class="form-group{{ hasError('name') }}">
                    <input type="text" class="form-control" id="name" name="name" maxlength="50" value="{{ getInput('name') }}" placeholder="{{ trans('boards.category') }}" required>
                </div>

                <button class="btn btn-primary">{{ trans('main.create') }}</button>
            </div>
            {!! textError('name') !!}
        </form>
    </div>
@stop
