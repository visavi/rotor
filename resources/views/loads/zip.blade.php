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

            <li class="breadcrumb-item"><a href="{{ route('downs.view', ['id' => $down->id]) }}">{{ $down->title }}</a></li>
            <li class="breadcrumb-item active">{{ __('loads.view_archive') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    {{ __('main.total') }}: {{ $documents->total() }}
    <hr>

    @if ($documents->isNotEmpty())
        <div class="mb-3">
            @foreach ($documents as $document)
                @if ($document['isDir'])
                    <i class="far fa-folder-open"></i>
                    <b>{{ __('loads.directory') }} {{ rtrim($document['name'], '/') }}</b><br>
                @else
                    └─ {{ icons($document['ext']) }}

                    @if (in_array($document['ext'], $down->getViewExt(), true))
                        <a href="{{ route('downs.zip-view', ['id' => $file->id, 'fid' => $document['index']]) }}">{{ $document['name'] }}</a>
                    @else
                        {{ $document['name'] }}
                    @endif

                    ({{ formatSize($document['size']) }})<br>
                @endif
            @endforeach
        </div>
    @else
        {{ showError(__('loads.empty_archive')) }}
    @endif

    {{ $documents->links() }}
@stop
