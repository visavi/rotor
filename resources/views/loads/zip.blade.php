@extends('layout')

@section('title')
    {{ trans('loads.view_archive') }} {{ $down->title }}
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/loads">{{ trans('index.loads') }}</a></li>

            @if ($down->category->parent->id)
                <li class="breadcrumb-item"><a href="/loads/{{ $down->category->parent->id }}">{{ $down->category->parent->name }}</a></li>
            @endif

            <li class="breadcrumb-item"><a href="/loads/{{ $down->category->id }}">{{ $down->category->name }}</a></li>
            <li class="breadcrumb-item"><a href="/downs/{{ $down->id }}">{{ $down->title }}</a></li>
            <li class="breadcrumb-item active">{{ trans('loads.view_archive') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    {{ trans('loads.total_files') }}: {{ $page->total }}<hr>

    @if ($documents)
        @foreach ($documents as $key => $document)

            @if ($document->isFolder())
                <i class="fa fa-folder-open"></i>
                <b>{{ trans('loads.directory') }} {{ rtrim($document->getName(), '/') }}</b><br>
            @else
                <?php $ext = getExtension($document->getName()) ?>

                {!! icons($ext) !!}

                @if (in_array($ext, $viewExt, true))
                    <a href="/downs/zip/{{ $file->id }}/{{ $key }}">{{ $document->getName() }}</a>
                @else
                    {{ $document->getName() }}
                @endif

                ({{ formatSize($document->getSize()) }})<br>
            @endif

        @endforeach

        {!! pagination($page) !!}
    @else
        {!! showError(trans('loads.empty_archive')) !!}
    @endif
@stop
