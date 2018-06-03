@extends('layout')

@section('title')
    Публикация новой статьи
@stop

@section('content')

    <h1>Публикация новой статьи</h1>

    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/blogs">Блоги</a></li>
            <li class="breadcrumb-item active">Публикация новой статьи</li>
        </ol>
    </nav>

    <div class="form next">
        <form action="/blogs/create" method="post">
            <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

            <div class="form-group{{ hasError('cid') }}">
                <label for="inputCategory">Раздел</label>

                <?php $inputCategory = getInput('cid', $cid); ?>
                <select class="form-control" id="inputCategory" name="cid">

                    @foreach ($cats as $data)
                        <option value="{{ $data->id }}"{{ ($inputCategory == $data->id && ! $data->closed) ? ' selected' : '' }}{{ $data->closed ? ' disabled' : '' }}>{{ $data->name }}</option>

                        @if ($data->children->isNotEmpty())
                            @foreach($data->children as $datasub)
                                <option value="{{ $datasub->id }}"{{ ($inputCategory == $datasub->id && ! $datasub->closed) ? ' selected' : '' }}{{ $datasub->closed ? ' disabled' : '' }}>– {{ $datasub->name }}</option>
                            @endforeach
                        @endif
                    @endforeach

                </select>
                {!! textError('cid') !!}
            </div>

            <div class="form-group{{ hasError('title') }}">
                <label for="inputTitle">Заголовок:</label>
                <input type="text" class="form-control" id="inputTitle" name="title" maxlength="50" value="{{ getInput('title') }}" required>
                {!! textError('title') !!}
            </div>

            <div class="form-group{{ hasError('text') }}">
                <label for="text">Текст:</label>
                <textarea class="form-control markItUp" id="text" rows="5" name="text" required>{{ getInput('text') }}</textarea>
                {!! textError('text') !!}
            </div>

            <div class="form-group{{ hasError('tags') }}">
                <label for="inputTags">Метки:</label>
                <input type="text" class="form-control" id="inputTags" name="tags" maxlength="100" value="{{ getInput('tags') }}" required>
                {!! textError('tags') !!}
            </div>

            <div class="js-images">
                @if ($files->isNotEmpty())
                    @foreach ($files as $file)
                        <span class="js-image">
                            {!! resizeImage('/uploads/blogs/' . $file->hash, ['width' => 100, 'onclick' => 'return pasteImage(this);']) !!}
                            <a href="#" onclick="return deleteImage(this);" data-id="{{ $file->id }}" data-token="{{ $_SESSION['token'] }}"><i class="fas fa-times"></i></a>
                        </span>
                    @endforeach
                @endif
            </div>

            <div class="js-image-template d-none">
                <span class="js-image">
                    <img src="#" width="100" onclick="return pasteImage(this);" alt="" class="img-fluid">
                    <a href="#" onclick="return deleteImage(this);" data-token="{{ $_SESSION['token'] }}"><i class="fas fa-times"></i></a>
                </span>
            </div>

            <label class="btn btn-sm btn-secondary" for="image">
                <input id="image" type="file" name="image" onchange="return submitImage(this);" data-id="0" data-token="{{ $_SESSION['token'] }}" hidden>
                Прикрепить картинку&hellip;
            </label><br>

            <button class="btn btn-primary">Опубликовать</button>
        </form>
    </div><br>

    Рекомендация! Для разбивки статьи по страницам используйте тег [nextpage]<br>
    Метки статьи должны быть от 2 до 20 символов с общей длиной не более 50 символов<br><br>

    <a href="/rules">Правила</a> /
    <a href="/smiles">Смайлы</a> /
    <a href="/tags">Теги</a><br><br>
@stop
