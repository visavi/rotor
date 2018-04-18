@extends('layout')

@section('title')
    {{ $path ?? 'Редактирование страниц' }}
@stop

@section('content')

    <h1>{{ $path ?? 'Редактирование страниц' }}</h1>

    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">Панель</a></li>

            @if ($path)
                <li class="breadcrumb-item"><a href="/admin/files?path={{ ltrim(dirname($path), '.') }}/">{{ ltrim(dirname($path), '.') }}/</a></li>
            @endif

            <li class="breadcrumb-item active">{{ $path ?? 'Редактирование страниц' }}</li>
        </ol>
    </nav>

    @if ($files)

        <ul class="list-group">
            @foreach ($files as $file)

                @if (is_dir(RESOURCES.'/views/'.$path.$file))
                    <li class="list-group-item">
                        <div class="float-right">
                            <a href="/admin/files/delete?path={{ $path }}&amp;dirname={{ $file }}&amp;token={{ $_SESSION['token'] }}" onclick="return confirm('Вы действительно хотите удалить эту директорию')"><i class="fa fa-times"></i></a>
                        </div>

                        <i class="fa fa-folder"></i> <b><a href="/admin/files?path={{ $path.$file }}/">{{ $file }}</a></b><br>
                        Объектов: {{ count(array_diff(scandir(RESOURCES.'/views/'.$path.$file), ['.', '..'])) }}
                    </li>
                @else

                    <?php $size = formatSize(filesize(RESOURCES.'/views/'.$path.$file)); ?>
                    <?php $string = count(file(RESOURCES.'/views/'.$path.$file)); ?>

                    <li class="list-group-item">
                        <div class="float-right">
                            <a href="/admin/files/delete?path={{ $path }}&amp;filename={{ basename($file, '.blade.php') }}&amp;token={{ $_SESSION['token'] }}" onclick="return confirm('Вы действительно хотите удалить этот файл')"><i class="fa fa-times"></i></a>
                        </div>

                        <i class="fa fa-file"></i>
                        <b><a href="/admin/files/edit?path={{ $path }}&amp;file={{ basename($file, '.blade.php') }}">{{ $file }}</a></b> ({{ $size }})<br>
                        Строк: {{ $string }} /
                        Изменен: {{ dateFixed(filemtime(RESOURCES.'/views/'.$path.$file)) }}
                    </li>
                @endif
            @endforeach
        </ul>
    @else
        {!! showError('Файлов нет!') !!}
    @endif

    <i class="fa fa-plus"></i> <a href="/admin/files/create?path={{ $path }}">Создать</a><br>
@stop
