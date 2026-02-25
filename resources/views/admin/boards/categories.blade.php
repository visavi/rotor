@extends('layout')

@section('title', __('boards.categories'))

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">{{ __('index.panel') }}</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.boards.index') }}">{{ __('index.boards') }}</a></li>
            <li class="breadcrumb-item active">{{ __('boards.categories') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    @foreach ($boards as $board)
        <div class="section mb-3 shadow">
            <div class="section-title">
                <i class="fa fa-folder-open"></i> <a href="{{ route('admin.boards.index', ['id' => $board->id]) }}">{{ $board->name }}</a>

                <span class="badge bg-adaptive">{{ $board->count_items }}</span>

                <div class="float-end">
                    <a href="{{ route('admin.boards.edit', ['id' => $board->id]) }}"><i class="fa fa-pencil-alt"></i></a>
                    <form action="{{ route('admin.boards.delete', ['id' => $board->id]) }}" method="post" class="d-inline" onsubmit="return confirm('{{ __('boards.confirm_delete_category') }}')">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-link p-0"><i class="fa fa-times"></i></button>
                    </form>
                </div>
            </div>

            <div class="section-content">
                @if ($board->children->isNotEmpty())
                    @foreach ($board->children as $child)
                        <div>
                            <i class="fa fa-angle-right"></i> <b><a href="{{ route('admin.boards.index', ['id' => $child->id ]) }}">{{ $child->name }}</a></b>

                            <span class="badge bg-adaptive">{{ $child->count_items }}</span>

                            <a href="{{ route('admin.boards.edit', ['id' => $child->id]) }}"><i class="fa fa-pencil-alt"></i></a>
                            <form action="{{ route('admin.boards.delete', ['id' => $child->id]) }}" method="post" class="d-inline" onsubmit="return confirm('{{ __('boards.confirm_delete_category') }}')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-link p-0"><i class="fa fa-times"></i></button>
                            </form>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>
    @endforeach

    <div class="section-form mb-3 shadow">
        <form action="{{ route('admin.boards.create') }}" method="post">
            @csrf
            <div class="input-group{{ hasError('name') }}">
                <input type="text" class="form-control" id="name" name="name" maxlength="{{ setting('board_category_max') }}" value="{{ getInput('name') }}" placeholder="{{ __('boards.category') }}" required>
                <button class="btn btn-primary">{{ __('main.create') }}</button>
            </div>
            <div class="invalid-feedback">{{ textError('name') }}</div>
        </form>
    </div>
@stop
