@extends('layout')

@section('title')
    {{ trans('boards.create_item') }}
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/boards">{{ trans('boards.title') }}</a></li>
            <li class="breadcrumb-item active">{{ trans('boards.create_item') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <form action="/items/create" method="post" enctype="multipart/form-data">
        @csrf
        <div class="form-group{{ hasError('category') }}">
            <label for="inputCategory">{{ trans('boards.category') }}</label>

            <select class="form-control" id="inputCategory" name="bid">
                @foreach ($boards as $board)

                    <option value="{{ $board->id }}"{{ ($bid === $board->id && ! $board->closed) ? ' selected' : '' }}{{ $board->closed ? ' disabled' : '' }}>{{ $board->name }}</option>

                    @if ($board->children->isNotEmpty())
                        @foreach($board->children as $boardsub)
                            <option value="{{ $boardsub->id }}"{{ $bid === $boardsub->id && ! $boardsub->closed ? ' selected' : '' }}{{ $boardsub->closed ? ' disabled' : '' }}>– {{ $boardsub->name }}</option>
                        @endforeach
                    @endif
                @endforeach

            </select>
            <div class="invalid-feedback">{{ textError('category') }}</div>
        </div>

        <div class="form-group{{ hasError('title') }}">
            <label for="inputTitle">{{ trans('boards.name') }}:</label>
            <input class="form-control" id="inputTitle" name="title" value="{{ getInput('title') }}" required>
            <div class="invalid-feedback">{{ textError('title') }}</div>
        </div>

        <div class="form-group{{ hasError('text') }}">
            <label for="text">{{ trans('boards.text') }}:</label>
            <textarea class="form-control markItUp" id="text" rows="10" name="text" required>{{ getInput('text') }}</textarea>
            <div class="invalid-feedback">{{ textError('text') }}</div>
        </div>

        <div class="form-group{{ hasError('price') }}">
            <label for="inputPrice">{{ trans('boards.price') }} {{ setting('currency') }}:</label>
            <input class="form-control" id="inputPrice" name="price" value="{{ getInput('price') }}" required>
            <div class="invalid-feedback">{{ textError('price') }}</div>
        </div>

        <div class="form-group{{ hasError('phone') }}">
            <label for="inputPhone">{{ trans('boards.phone') }}:</label>
            <input class="phone form-control" id="inputPhone" name="phone" placeholder="8 ___ ___-__-__" maxlength="15" value="{{ getInput('phone', getUser('phone')) }}">
            <div class="invalid-feedback">{{ textError('phone') }}</div>
        </div>

        @include('app/_upload', ['files' => $files, 'type' => App\Models\Item::class])

        <button class="btn btn-primary">{{ trans('main.add') }}</button>
    </form>
@stop
