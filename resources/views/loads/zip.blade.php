@extends('layout')

@section('title', __('loads.view_archive') . ' ' . $down->title)

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/loads">{{ __('index.loads') }}</a></li>

            @foreach ($down->category->getParents() as $parent)
                <li class="breadcrumb-item"><a href="/loads/{{ $parent->id }}">{{ $parent->name }}</a></li>
            @endforeach

            <li class="breadcrumb-item"><a href="/downs/{{ $down->id }}">{{ $down->title }}</a></li>
            <li class="breadcrumb-item active">{{ __('loads.view_archive') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    {{ __('main.total') }}: {{ $documents->total() }}
    <hr>

    @if ($documents->isNotEmpty())
        <div class="mb-3">
            @foreach ($documents as $key => $document)
                @if ($document->isDirectory())
                    <i class="far fa-folder-open"></i>
                    <b>{{ __('loads.directory') }} {{ rtrim($document->getName(), '/') }}</b><br>
                @else
                    @php $ext = getExtension($document->getName()) @endphp

                    └─ {{ icons($ext) }}

                    @if (in_array($ext, $viewExt, true))
                        <a href="/downs/zip/{{ $file->id }}/{{ $key }}">{{ $document->getName() }}</a>
                    @else
                        {{ $document->getName() }}
                    @endif

                    ({{ formatSize($document->getUncompressedSize()) }})<br>
                @endif
            @endforeach
        </div>
    @else
        {{ showError(__('loads.empty_archive')) }}
    @endif

    {{ $documents->links() }}
@stop
