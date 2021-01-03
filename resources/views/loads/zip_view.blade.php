@extends('layout')

@section('title', __('loads.view_file') . ' ' . $document->getName())

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/loads">{{ __('index.loads') }}</a></li>

            @if ($down->category->parent->id)
                <li class="breadcrumb-item"><a href="/loads/{{ $down->category->parent->id }}">{{ $down->category->parent->name }}</a></li>
            @endif

            <li class="breadcrumb-item"><a href="/loads/{{ $down->category->id }}">{{ $down->category->name }}</a></li>
            <li class="breadcrumb-item"><a href="/downs/{{ $down->id }}">{{ $down->title }}</a></li>
            <li class="breadcrumb-item"><a href="/downs/zip/{{ $file->id }}">{{ __('loads.view_archive') }}</a></li>
            <li class="breadcrumb-item active">{{ __('loads.view_file') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    {{ __('loads.file_size') }}: {{ formatSize($document->getSize()) }}
    <hr>

    @if ($content)
        <div class="mb-3">
            <pre class="prettyprint linenums">{{ $content }}</pre>
        </div>
    @else
        {!! showError(__('loads.empty_file')) !!}
    @endif
@stop
