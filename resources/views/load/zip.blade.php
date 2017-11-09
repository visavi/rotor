@extends('layout')

@section('title')
    Просмотр архива {{ $down->title }}
@stop

@section('content')
    <h1>Просмотр архива {{ $down->title }}</h1>

    Всего файлов: {{ $page['total'] }}<hr>

    @if ($members)
        @foreach ($members as $member)

            @if ($member->isDir())
                <i class="fa fa-folder-open-o"></i>
                <b>Директория {{ substr($member->getLocation(), 0, -1) }}</b><br>
            @else
                <?php $ext = getExtension($member->getLocation()); ?>

                {!! icons($ext) !!}

                @if (in_array($ext, $viewExt))
                    <a href="#">{{ $member->getLocation() }}</a>
                @else
                    {{ $member->getLocation() }}
                @endif
                {{ formatSize($member->getSize()) }}<br>
            @endif

        @endforeach

        {{ pagination($page) }}
    @else
        {{ showError('В данном архиве нет файлов!') }}
    @endif


    <i class="fa fa-arrow-circle-left"></i> <a href="/down/{{ $down->id }}">Вернуться</a><br>
    <i class="fa fa-arrow-circle-up"></i> <a href="/load">Категории</a><br>
@stop
