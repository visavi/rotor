@extends('layout')

@section('title', __('boards.categories'))

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ __('index.panel') }}</a></li>
            <li class="breadcrumb-item"><a href="/admin/boards">{{ __('index.boards') }}</a></li>
            <li class="breadcrumb-item active">{{ __('boards.categories') }}</li>
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
                <a href="/admin/boards/delete/{{ $board->id }}?token={{ $_SESSION['token'] }}" onclick="return confirm('{{ __('boards.confirm_delete_category') }}')"><i class="fa fa-times"></i></a>
            </div>
        </div>

        <div>
            @if ($board->children->isNotEmpty())
                @foreach ($board->children as $child)
                    <i class="fa fa-angle-right"></i> <b><a href="/admin/boards/{{ $child->id }}">{{ $child->name }}</a></b>

                    ({{ $child->count_items }})

                    <a href="/admin/boards/edit/{{ $child->id }}"><i class="fa fa-pencil-alt"></i></a>
                    <a href="/admin/boards/delete/{{ $child->id }}?token={{ $_SESSION['token'] }}" onclick="return confirm('{{ __('boards.confirm_delete_category') }}')"><i class="fa fa-times"></i></a>
                    <br/>
                @endforeach
            @endif
        </div>
    @endforeach

    <div class="section-form my-3">
        <form action="/admin/boards/create" method="post">
            @csrf
            <div class="form-inline">
                <div class="form-group{{ hasError('name') }}">
                    <input type="text" class="form-control" id="name" name="name" maxlength="50" value="{{ getInput('name') }}" placeholder="{{ __('boards.category') }}" required>
                </div>

                <button class="btn btn-primary">{{ __('main.create') }}</button>
            </div>
            <div class="invalid-feedback">{{ textError('name') }}</div>
        </form>
    </div>
@stop
