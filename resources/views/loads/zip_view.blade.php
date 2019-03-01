@extends('layout')

@section('title')
    Просмотр файла {{ $document->getName() }}
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
            <li class="breadcrumb-item"><a href="/downs/zip/{{ $file->id }}">Архив</a></li>
            <li class="breadcrumb-item active">Просмотр файла</li>
        </ol>
    </nav>
@stop

@section('content')
    Размер файла: {{ formatSize($document->getSize()) }}<hr>

    @if ($content)
        <pre class="prettyprint linenums">{{ $content }}</pre><br>
    @else
        {!! showError('Данный файл пустой!') !!}
    @endif
@stop
