@extends('layout')

@section('title', $path ?? __('index.page_editor'))

@section('header')
    @if (getUser())
        <div class="float-end">
            <a class="btn btn-success" href="/admin/files/create?path={{ $path }}">{{ __('main.create') }}</a><br>
        </div>
    @endif

    <h1>{{ $path ?? __('index.page_editor') }}</h1>
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">{{ __('index.panel') }}</a></li>

            @if ($path)
                <li class="breadcrumb-item"><a href="/admin/files">{{ __('index.page_editor') }}</a></li>

                <?php $dirNames = []; ?>
                @foreach ($directories as $directory)
                    <?php $dirNames[] = $directory; ?>
                    @if ($path !== implode('/', $dirNames))
                        <li class="breadcrumb-item"><a href="/admin/files?path={{ implode('/', $dirNames) }}">{{ implode('/', $dirNames) }}</a></li>
                    @endif
                @endforeach
            @endif

            <li class="breadcrumb-item active">{{ $path ?? __('index.page_editor') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    @if ($files)
        <ul class="list-group">
            @foreach ($files as $file)
                <?php $fileName = $path ? '/' . $file : $file; ?>
                @if (is_dir(resource_path('views/' . $path . $fileName)))
                    <li class="list-group-item">
                        <div class="float-end">
                            <form action="/admin/files/delete" method="post" class="d-inline" onsubmit="return confirm('{{ __('admin.files.confirm_delete_dir') }}')">
                                @csrf
                                @method('DELETE')
                                <input type="hidden" name="path" value="{{ $path }}">
                                <input type="hidden" name="dirname" value="{{ $file }}">
                                <button class="btn btn-link p-0"><i class="fa fa-times"></i></button>
                            </form>
                        </div>

                        <i class="fa fa-folder"></i> <b><a href="/admin/files?path={{ $path . $fileName }}">{{ $file }}</a></b><br>
                        {{ __('admin.files.objects') }}: {{ count(array_diff(scandir(resource_path('views/' . $path . $fileName)), ['.', '..'])) }}
                    </li>
                @else
                    <?php $size = formatSize(filesize(resource_path('views/' . $path . $fileName))); ?>
                    <?php $string = count(file(resource_path('views/' . $path . $fileName))); ?>

                    <li class="list-group-item">
                        <div class="float-end">
                            <form action="/admin/files/delete" method="post" class="d-inline" onsubmit="return confirm('{{ __('admin.files.confirm_delete_file') }}')">
                                @csrf
                                @method('DELETE')
                                <input type="hidden" name="path" value="{{ $path }}">
                                <input type="hidden" name="filename" value="{{ basename($file, '.blade.php') }}">
                                <button class="btn btn-link p-0"><i class="fa fa-times"></i></button>
                            </form>
                        </div>

                        <i class="fa fa-file"></i>
                        <b><a href="/admin/files/edit?path={{ $path }}&amp;file={{ basename($file, '.blade.php') }}">{{ $file }}</a></b> ({{ $size }})<br>
                        {{ __('admin.files.lines') }}: {{ $string }} /
                        {{ __('admin.files.changed') }}: {{ dateFixed(filemtime(resource_path('views/' . $path . $fileName))) }}
                    </li>
                @endif
            @endforeach
        </ul>
    @else
        {{ showError(__('admin.files.empty_objects')) }}
    @endif
@stop
