@extends('layout')

@section('title', __('boards.edit_item'))

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('boards.index') }}">{{ __('index.boards') }}</a></li>

            @foreach ($item->category->getParents() as $parent)
                <li class="breadcrumb-item"><a href="{{ route('admin.boards.index', ['id' => $parent->id]) }}">{{ $parent->name }}</a></li>
            @endforeach

            <li class="breadcrumb-item"><a href="{{ route('items.view', ['id' => $item->id]) }}">{{ $item->title }}</a></li>
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

    <i class="fas fa-times"></i> <a class="me-3" href="{{ route('admin.items.delete', ['id' => $item->id, '_token' => csrf_token()]) }}" onclick="return confirm('{{ __('boards.confirm_delete_item') }}')">{{ __('main.delete') }}</a>
    <hr>

    <div class="section-form mb-3 shadow">
        <form action="{{ route('admin.items.edit', ['id' => $item->id]) }}" method="post">
            @csrf
            <div class="mb-3{{ hasError('bid') }}">
                <label for="inputCategory" class="form-label">{{ __('boards.category') }}</label>

                <?php $inputCategory = (int) getInput('bid', $item->board_id); ?>
                <select class="form-select" id="inputCategory" name="bid">

                    @foreach ($boards as $data)
                        <option value="{{ $data->id }}"{{ ($inputCategory === $data->id && ! $data->closed) ? ' selected' : '' }}{{ $data->closed ? ' disabled' : '' }}>
                            {{ str_repeat('–', $data->depth) }} {{ $data->name }}
                        </option>
                    @endforeach

                </select>
                <div class="invalid-feedback">{{ textError('bid') }}</div>
            </div>

            <div class="mb-3{{ hasError('title') }}">
                <label for="inputTitle" class="form-label">{{ __('boards.name') }}:</label>
                <input type="text" class="form-control" id="inputTitle" name="title" maxlength="50" value="{{ getInput('title', $item->title) }}" required>
                <div class="invalid-feedback">{{ textError('title') }}</div>
            </div>

            <div class="mb-3{{ hasError('text') }}">
                <label for="text" class="form-label">{{ __('boards.text') }}:</label>
                <textarea class="form-control markItUp" id="text" rows="5" name="text" required>{{ getInput('text', $item->text) }}</textarea>
                <div class="invalid-feedback">{{ textError('text') }}</div>
            </div>

            <div class="mb-3{{ hasError('price') }}">
                <label for="inputPrice" class="form-label">{{ __('boards.price') }}:</label>
                <input type="text" class="form-control" id="inputPrice" name="price" value="{{ getInput('price', $item->price) }}" required>
                <div class="invalid-feedback">{{ textError('price') }}</div>
            </div>

            <div class="mb-3{{ hasError('phone') }}">
                <label for="inputPhone" class="form-label">{{ __('boards.phone') }}:</label>
                <input class="phone form-control" id="inputPhone" name="phone" placeholder="+7 ___ ___-__-__" maxlength="18" value="{{ getInput('phone', $item->phone) }}">
                <div class="invalid-feedback">{{ textError('phone') }}</div>
            </div>

            @include('app/_upload_image', [
                'id'    => $item->id,
                'files' => $item->files,
                'type'  => $item->getMorphClass(),
            ])

            <button class="btn btn-primary">{{ __('main.change') }}</button>
        </form>
    </div>
@stop
