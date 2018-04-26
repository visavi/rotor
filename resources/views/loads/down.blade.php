@extends('layout')

@section('title')
    {{ $down->title }}
@stop

@section('description', stripString($down->text))

@section('content')

    <h1>{{ $down->title }}</h1>

    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/loads">Загрузки</a></li>

            @if ($down->category->parent->id)
                <li class="breadcrumb-item"><a href="/loads/{{ $down->category->parent->id }}">{{ $down->category->parent->name }}</a></li>
            @endif

            <li class="breadcrumb-item"><a href="/loads/{{ $down->category_id }}">{{ $down->category->name }}</a></li>
            <li class="breadcrumb-item active">{{ $down->title }}</li>
            <li class="breadcrumb-item"><a href="/downs/rss/{{ $down->id }}">RSS-лента</a></li>

            @if (isAdmin('admin'))
                <li class="breadcrumb-item"><a href="/admin/downs/edit/{{ $down->id }}">Редактировать</a></li>
            @endif
        </ol>
    </nav>

    @if (! $down->active)
        <div class="p-1 bg-warning text-dark">
            <b>Внимание!</b> Данная загрузка ожидает проверки модератором!<br>
            @if ($down->user_id == getUser('id'))
                <i class="fa fa-pencil-alt"></i> <a href="/downs/edit/{{ $down->id }}">Перейти к редактированию</a>
            @endif
        </div><br>
    @endif

    <div class="message">
        {!! bbCode($down->text) !!}
    </div><br>

    @if ($down->files->isNotEmpty())

        @if ($down->getFiles()->isNotEmpty())
            @foreach ($down->getFiles() as $file)
                @if (file_exists(UPLOADS.'/files/'.$file->hash))
                    <div class="mt-3">
                        <i class="fa fa-download"></i> <b><a href="/downs/download/{{ $file->id }}">{{ $file->name }}</a></b> ({{ formatSize($file->size) }})

                        @if ($file->extension === 'mp3')
                            <audio preload="none" controls style="max-width:100%;">
                                <source src="/uploads/files/{{ $file->hash }}" type="audio/mp3">
                            </audio>
                        @endif

                        @if ($file->extension === 'mp4')
                            <?php $poster = file_exists(UPLOADS . '/screens/' . $file->hash . '.jpg') ? '/uploads/screens/' . $file->hash . '.jpg' : null; ?>

                           <video width="640" height="360" style="max-width:100%;" poster="{{ $poster }}" preload="none" controls playsinline>
                               <source src="/uploads/files/{{ $file->hash }}" type="video/mp4">
                           </video>
                        @endif

                        @if ($file->extension === 'zip')
                            <a href="/downs/zip/{{ $file->id }}">Просмотреть архив</a><br>
                        @endif
                    </div>
                @endif
            @endforeach
        @endif

        @if ($down->getImages()->isNotEmpty())
            <div class="mt-3">
                @foreach ($down->getImages() as $image)
                    <a href="/uploads/screens/{{ $image->hash }}" class="gallery" data-group="{{ $down->id }}">{!! resizeImage('/uploads/screens/' . $image->hash, ['alt' => $down->title]) !!}</a><br>
                @endforeach
            </div>
        @endif
    @else
        {!! showError('Файлы еще не загружены!') !!}
    @endif

    <div class="mt-3">
        <i class="fa fa-comment"></i> <a href="/downs/comments/{{ $down->id }}">Комментарии</a> ({{ $down->count_comments }})
        <a href="/downs/end/{{ $down->id }}">&raquo;</a><br>

        Рейтинг: {!! ratingVote($rating) !!}<br>
        Всего голосов: <b>{{ $down->rated }}</b><br>
        Всего скачиваний: <b>{{ $down->loads }}</b><br>
        Добавлено: {!! profile($down->user) !!} ({{ dateFixed($down->created_at) }})<br><br>
    </div>

    @if (getUser() && getUser('id') != $down->user_id)
        <form class="form-inline" action="/downs/votes/{{ $down->id }}" method="post">
            <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

            <div class="form-group{{ hasError('score') }}">
                <select class="form-control" id="score" name="score">
                    <option value="5" {{ $down->vote == 5 ? ' selected' : '' }}>Отлично</option>
                    <option value="4" {{ $down->vote == 4 ? ' selected' : '' }}>Хорошо</option>
                    <option value="3" {{ $down->vote == 3 ? ' selected' : '' }}>Нормально</option>
                    <option value="2" {{ $down->vote == 2 ? ' selected' : '' }}>Плохо</option>
                    <option value="1" {{ $down->vote == 1 ? ' selected' : '' }}>Отстой</option>
                </select>
                {!! textError('protect') !!}
            </div>
            <button class="btn btn-primary">Оценить</button>
        </form>
    @endif
@stop
