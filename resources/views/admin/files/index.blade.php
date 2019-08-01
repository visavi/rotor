@extends('layout')

@section('title')
    {{ $path ?? trans('index.pages_editing') }}
@stop

@section('header')
    @if (getUser())
        <div class="float-right">
            <a class="btn btn-success" href="/admin/files/create?path={{ $path }}">{{ trans('main.create') }}</a><br>
        </div><br>
    @endif

    <h1>{{ $path ?? trans('index.pages_editing') }}</h1>
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ trans('index.panel') }}</a></li>

            @if ($path)
                <li class="breadcrumb-item"><a href="/admin/files">{{ trans('index.pages_editing') }}</a></li>

                <?php $dirNames = []; ?>
                @foreach ($directories as $directory)
                    <?php $dirNames[] = $directory; ?>
                    @if ($path !== implode('/', $dirNames))
                        <li class="breadcrumb-item"><a href="/admin/files?path={{ implode('/', $dirNames) }}">{{ implode('/', $dirNames) }}</a></li>
                    @endif
                @endforeach
            @endif

            <li class="breadcrumb-item active">{{ $path ?? trans('index.pages_editing') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    @if ($files)
        <ul class="list-group">
            @foreach ($files as $file)
                <?php $fileName = $path ? '/' . $file : $file; ?>
                @if (is_dir(RESOURCES . '/views/' . $path . $fileName))
                    <li class="list-group-item">
                        <div class="float-right">
                            <a href="/admin/files/delete?path={{ $path }}&amp;dirname={{ $file }}&amp;token={{ $_SESSION['token'] }}" onclick="return confirm('{{ trans('admin.files.confirm_delete_dir') }}')"><i class="fa fa-times"></i></a>
                        </div>

                        <i class="fa fa-folder"></i> <b><a href="/admin/files?path={{ $path . $fileName }}">{{ $file }}</a></b><br>
                        {{ trans('admin.files.objects') }}: {{ count(array_diff(scandir(RESOURCES . '/views/' . $path . $fileName), ['.', '..'])) }}
                    </li>
                @else
                    <?php $size = formatSize(filesize(RESOURCES . '/views/' . $path . $fileName)); ?>
                    <?php $string = count(file(RESOURCES . '/views/' . $path . $fileName)); ?>

                    <li class="list-group-item">
                        <div class="float-right">
                            <a href="/admin/files/delete?path={{ $path }}&amp;filename={{ basename($file, '.blade.php') }}&amp;token={{ $_SESSION['token'] }}" onclick="return confirm('{{ trans('admin.files.confirm_delete_file') }}')"><i class="fa fa-times"></i></a>
                        </div>

                        <i class="fa fa-file"></i>
                        <b><a href="/admin/files/edit?path={{ $path }}&amp;file={{ basename($file, '.blade.php') }}">{{ $file }}</a></b> ({{ $size }})<br>
                        {{ trans('admin.files.lines') }}: {{ $string }} /
                        {{ trans('admin.files.changed') }}: {{ dateFixed(filemtime(RESOURCES . '/views/' . $path . $fileName)) }}
                    </li>
                @endif
            @endforeach
        </ul>
    @else
        {!! showError(trans('admin.files.empty_objects')) !!}
    @endif
@stop
