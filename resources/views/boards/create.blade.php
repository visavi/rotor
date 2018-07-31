@extends('layout')

@section('title')
    Добавление объявления
@stop

@section('content')

    <h1>Добавление объявления</h1>

    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/boards">Объявления</a></li>
            <li class="breadcrumb-item active">Добавление объявления</li>
        </ol>
    </nav>

    <form action="/items/create" method="post" enctype="multipart/form-data">
        <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

        <div class="form-group{{ hasError('category') }}">
            <label for="inputCategory">Категория</label>

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
            {!! textError('category') !!}
        </div>

        <div class="form-group{{ hasError('title') }}">
            <label for="inputTitle">Название:</label>
            <input class="form-control" id="inputTitle" name="title" value="{{ getInput('title') }}" required>
            {!! textError('title') !!}
        </div>

        <div class="form-group{{ hasError('text') }}">
            <label for="text">Описание:</label>
            <textarea class="form-control markItUp" id="text" rows="10" name="text" required>{{ getInput('text') }}</textarea>
            {!! textError('text') !!}
        </div>

        <div class="form-group{{ hasError('price') }}">
            <label for="inputPrice">Цена ₽:</label>
            <input class="form-control" id="inputPrice" name="price" value="{{ getInput('price') }}" required>
            {!! textError('price') !!}
        </div>

        <div class="form-group{{ hasError('phone') }}">
            <label for="inputPhone">Телефон:</label>
            <input class="phone form-control" id="inputPhone" name="phone" placeholder="8 ___ ___-__-__" maxlength="15" value="{{ getInput('phone', getUser('phone')) }}">
            {!! textError('phone') !!}
        </div>

        @include('app._upload', ['files' => $files, 'type' => App\Models\Item::class])

        <button class="btn btn-primary">Добавить</button>
    </form>
@stop
