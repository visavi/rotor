@extends('layout')

@section('title')
    Редактирование объявления
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/boards">Блоги</a></li>

            @if ($item->category->parent->id)
                <li class="breadcrumb-item"><a href="/boards/{{ $item->category->parent->id }}">{{ $item->category->parent->name }}</a></li>
            @endif

            <li class="breadcrumb-item"><a href="/boards/{{ $item->category->id }}">{{ $item->category->name }}</a></li>
            <li class="breadcrumb-item"><a href="/items/{{ $item->id }}">{{ $item->title }}</a></li>
            <li class="breadcrumb-item active">Редактирование</li>
        </ol>
    </nav>
@stop

@section('content')
    @if ($item->expires_at > SITETIME)
        <div class="alert alert-info">Истекает: {{ dateFixed($item->expires_at) }}</div>
    @else
        <div class="alert alert-danger">Объявление не активно</div>
    @endif

    <a href="/admin/items/delete/{{ $item->id }}?token={{ $_SESSION['token'] }}" onclick="return confirm('Вы действительно хотите удалить объявление?')">Удалить</a>

    <div class="form">
        <form action="/admin/items/edit/{{ $item->id }}" method="post">
            <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

            <div class="form-group{{ hasError('bid') }}">
                <label for="inputCategory">Раздел</label>

                <?php $inputCategory = getInput('bid', $item->board_id); ?>
                <select class="form-control" id="inputCategory" name="bid">

                    @foreach ($boards as $data)
                        <option value="{{ $data->id }}"{{ ($inputCategory === $data->id && ! $data->closed) ? ' selected' : '' }}{{ $data->closed ? ' disabled' : '' }}>{{ $data->name }}</option>

                        @if ($data->children->isNotEmpty())
                            @foreach($data->children as $datasub)
                                <option value="{{ $datasub->id }}"{{ ($inputCategory === $datasub->id && ! $data->closed) ? ' selected' : '' }}{{ $datasub->closed ? ' disabled' : '' }}>– {{ $datasub->name }}</option>
                            @endforeach
                        @endif
                    @endforeach

                </select>
                {!! textError('bid') !!}
            </div>

            <div class="form-group{{ hasError('title') }}">
                <label for="inputTitle">Название:</label>
                <input type="text" class="form-control" id="inputTitle" name="title" maxlength="50" value="{{ getInput('title', $item->title) }}" required>
                {!! textError('title') !!}
            </div>

            <div class="form-group{{ hasError('text') }}">
                <label for="text">Текст:</label>
                <textarea class="form-control markItUp" id="text" rows="5" name="text" required>{{ getInput('text', $item->text) }}</textarea>
                {!! textError('text') !!}
            </div>

            <div class="form-group{{ hasError('price') }}">
                <label for="inputPrice">Цена:</label>
                <input type="text" class="form-control" id="inputPrice" name="price" value="{{ getInput('price', $item->price) }}" required>
                {!! textError('price') !!}
            </div>

            <div class="form-group{{ hasError('phone') }}">
                <label for="inputPhone">Телефон:</label>
                <input class="phone form-control" id="inputPhone" name="phone" placeholder="8 ___ ___-__-__" maxlength="15" value="{{ getInput('phone', $item->phone) }}">
                {!! textError('phone') !!}
            </div>

            <div class="js-images">
                @if ($item->files->isNotEmpty())
                    @foreach ($item->files as $file)
                        <span class="js-image">
                            {!! resizeImage($file->hash, ['width' => 100]) !!}
                            <a href="#" onclick="return deleteImage(this);" data-id="{{ $file->id }}" data-type="{{ App\Models\Item::class }}" data-token="{{ $_SESSION['token'] }}"><i class="fas fa-times"></i></a>
                        </span>
                    @endforeach
                @endif
            </div>

            <button class="btn btn-primary">Изменить</button>
        </form>
    </div>
@stop
