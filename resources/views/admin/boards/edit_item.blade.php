@extends('layout')

@section('title')
    {{ __('boards.edit_item') }}
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/boards">{{ __('index.boards') }}</a></li>

            @if ($item->category->parent->id)
                <li class="breadcrumb-item"><a href="/boards/{{ $item->category->parent->id }}">{{ $item->category->parent->name }}</a></li>
            @endif

            <li class="breadcrumb-item"><a href="/boards/{{ $item->category->id }}">{{ $item->category->name }}</a></li>
            <li class="breadcrumb-item"><a href="/items/{{ $item->id }}">{{ $item->title }}</a></li>
            <li class="breadcrumb-item active">{{ __('boards.edit_item') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    @if ($item->expires_at > SITETIME)
        <div class="alert alert-info">{{ __('boards.expires') }}: {{ dateFixed($item->expires_at) }}</div>
    @else
        <div class="alert alert-danger">{{ __('boards.item_not_active') }}</div>
    @endif

    <a href="/admin/items/delete/{{ $item->id }}?token={{ $_SESSION['token'] }}" onclick="return confirm('{{ __('boards.confirm_delete') }}')">{{ __('main.delete') }}</a>

    <div class="section-form p-2 shadow">
        <form action="/admin/items/edit/{{ $item->id }}" method="post">
            @csrf
            <div class="form-group{{ hasError('bid') }}">
                <label for="inputCategory">{{ __('boards.category') }}</label>

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
                <div class="invalid-feedback">{{ textError('bid') }}</div>
            </div>

            <div class="form-group{{ hasError('title') }}">
                <label for="inputTitle">{{ __('boards.name') }}:</label>
                <input type="text" class="form-control" id="inputTitle" name="title" maxlength="50" value="{{ getInput('title', $item->title) }}" required>
                <div class="invalid-feedback">{{ textError('title') }}</div>
            </div>

            <div class="form-group{{ hasError('text') }}">
                <label for="text">{{ __('boards.text') }}:</label>
                <textarea class="form-control markItUp" id="text" rows="5" name="text" required>{{ getInput('text', $item->text) }}</textarea>
                <div class="invalid-feedback">{{ textError('text') }}</div>
            </div>

            <div class="form-group{{ hasError('price') }}">
                <label for="inputPrice">{{ __('boards.price') }}:</label>
                <input type="text" class="form-control" id="inputPrice" name="price" value="{{ getInput('price', $item->price) }}" required>
                <div class="invalid-feedback">{{ textError('price') }}</div>
            </div>

            <div class="form-group{{ hasError('phone') }}">
                <label for="inputPhone">{{ __('boards.phone') }}:</label>
                <input class="phone form-control" id="inputPhone" name="phone" placeholder="8 ___ ___-__-__" maxlength="15" value="{{ getInput('phone', $item->phone) }}">
                <div class="invalid-feedback">{{ textError('phone') }}</div>
            </div>

            <div class="js-images">
                @if ($item->files->isNotEmpty())
                    @foreach ($item->files as $file)
                        <span class="js-image">
                            {!! resizeImage($file->hash, ['width' => 100]) !!}
                            <a href="#" onclick="return deleteImage(this);" data-id="{{ $file->id }}" data-type="{{ $item->getMorphClass() }}" data-token="{{ $_SESSION['token'] }}"><i class="fas fa-times"></i></a>
                        </span>
                    @endforeach
                @endif
            </div>

            <button class="btn btn-primary">{{ __('main.change') }}</button>
        </form>
    </div>
@stop
