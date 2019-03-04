@extends('layout')

@section('title')
    {{ trans('loads.view_file') }} {{ $document->getName() }}
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/loads">{{ trans('loads.title') }}</a></li>

            @if ($down->category->parent->id)
                <li class="breadcrumb-item"><a href="/loads/{{ $down->category->parent->id }}">{{ $down->category->parent->name }}</a></li>
            @endif

            <li class="breadcrumb-item"><a href="/loads/{{ $down->category->id }}">{{ $down->category->name }}</a></li>
            <li class="breadcrumb-item"><a href="/downs/{{ $down->id }}">{{ $down->title }}</a></li>
            <li class="breadcrumb-item"><a href="/downs/zip/{{ $file->id }}">{{ trans('loads.view_archive') }}</a></li>
            <li class="breadcrumb-item active">{{ trans('loads.view_file') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    {{ trans('loads.file_size') }}: {{ formatSize($document->getSize()) }}<hr>

    @if ($content)
        <pre class="prettyprint linenums">{{ $content }}</pre><br>
    @else
        {!! showError(trans('loads.empty_file')) !!}
    @endif
@stop
