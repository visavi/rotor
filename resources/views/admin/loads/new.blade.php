@extends('layout')

@section('title', __('loads.new_publications') . ' (' . __('main.page_num', ['page' => $downs->currentPage()]) . ')')

@section('header')
    <h1>{{ __('loads.new_publications') }}</h1>
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">{{ __('index.panel') }}</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.loads.index') }}">{{ __('index.loads') }}</a></li>
            <li class="breadcrumb-item active">{{ __('loads.new_publications') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    @if ($downs->isNotEmpty())
        @foreach ($downs as $data)
            <div class="section mb-3 shadow">
                <div class="section-header d-flex align-items-start">
                    <div class="flex-grow-1">
                        <div class="section-title">
                            <i class="fa fa-file"></i>
                            <a href="{{ route('downs.view', ['id' => $data->id]) }}">{{ $data->title }}</a> <span class="badge bg-adaptive">{{ $data->count_comments }}</span>
                        </div>
                    </div>

                    <div class="text-end">
                        <a href="{{ route('admin.downs.edit', ['id' => $data->id]) }}" title="{{ __('main.edit') }}"><i class="fa fa-pencil-alt"></i></a>

                        @if (isAdmin('boss'))
                            <form action="{{ route('admin.downs.delete', ['id' => $data->id]) }}" method="post" class="d-inline" onsubmit="return confirm('{{ __('loads.confirm_delete_down') }}')">
                                @csrf
                                <button class="btn btn-link p-0" title="{{ __('main.delete') }}"><i class="fa fa-times"></i></button>
                            </form>
                        @endif
                    </div>
                </div>

                <div class="section-content">
                    {{ __('loads.load') }}: <a href="{{ route('admin.loads.load', ['id' => $data->category->id]) }}">{{ $data->category->name }}</a><br>
                    {{ __('loads.files_images') }}: {{ $data->getFiles()->count() }}/{{ $data->getImages()->count() }}<br>
                    {{ __('main.author') }}: {{ $data->user->getProfile() }} <small class="section-date text-muted fst-italic">{{ dateFixed($data->created_at) }}</small>
                </div>
            </div>
        @endforeach
    @else
        {{ showError(__('loads.empty_downs')) }}
    @endif

    {{ $downs->links() }}
@stop
