@extends('layout')

@section('title', __('boards.create_item'))

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/boards">{{ __('index.boards') }}</a></li>
            <li class="breadcrumb-item active">{{ __('boards.create_item') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <form action="/items/create" method="post" enctype="multipart/form-data">
        @csrf
        <div class="mb-3{{ hasError('category') }}">
            <label for="inputCategory" class="form-label">{{ __('boards.category') }}</label>

            <select class="form-select" id="inputCategory" name="bid">
                @foreach ($boards as $board)

                    <option value="{{ $board->id }}"{{ ($bid === $board->id && ! $board->closed) ? ' selected' : '' }}{{ $board->closed ? ' disabled' : '' }}>{{ $board->name }}</option>

                    @if ($board->children->isNotEmpty())
                        @foreach ($board->children as $boardsub)
                            <option value="{{ $boardsub->id }}"{{ $bid === $boardsub->id && ! $boardsub->closed ? ' selected' : '' }}{{ $boardsub->closed ? ' disabled' : '' }}>– {{ $boardsub->name }}</option>
                        @endforeach
                    @endif
                @endforeach

            </select>
            <div class="invalid-feedback">{{ textError('category') }}</div>
        </div>

        <div class="mb-3{{ hasError('title') }}">
            <label for="inputTitle" class="form-label">{{ __('boards.name') }}:</label>
            <input class="form-control" id="inputTitle" name="title" value="{{ getInput('title') }}" required>
            <div class="invalid-feedback">{{ textError('title') }}</div>
        </div>

        <div class="mb-3{{ hasError('text') }}">
            <label for="text" class="form-label">{{ __('boards.text') }}:</label>
            <textarea class="form-control markItUp" id="text" rows="10" name="text" required>{{ getInput('text') }}</textarea>
            <div class="invalid-feedback">{{ textError('text') }}</div>
        </div>

        <div class="mb-3{{ hasError('price') }}">
            <label for="inputPrice" class="form-label">{{ __('boards.price') }} {{ setting('currency') }}:</label>
            <input class="form-control" id="inputPrice" name="price" value="{{ getInput('price') }}" required>
            <div class="invalid-feedback">{{ textError('price') }}</div>
        </div>

        <div class="mb-3{{ hasError('phone') }}">
            <label for="inputPhone" class="form-label">{{ __('boards.phone') }}:</label>
            <input class="phone form-control" id="inputPhone" name="phone" placeholder="8 ___ ___-__-__" maxlength="18" value="{{ getInput('phone', getUser('phone')) }}">
            <div class="invalid-feedback">{{ textError('phone') }}</div>
        </div>

        @include('app/_upload_image', ['files' => $files, 'type' => App\Models\Item::$morphName])

        <button class="btn btn-primary">{{ __('main.add') }}</button>
    </form>
@stop
