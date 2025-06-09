@extends('layout')

@section('title', __('index.loads') . ' - ' . __('loads.active_downs', ['user' => $user->getName()]) . ' (' . __('main.page_num', ['page' => $downs->currentPage()]) . ')')

@section('header')
    <h1>{{ __('loads.active_downs', ['user' => $user->getName()]) }}</h1>
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/loads">{{ __('index.loads') }}</a></li>
            <li class="breadcrumb-item active">{{ __('loads.active_downs', ['user' => $user->login]) }}</li>
        </ol>
    </nav>
@stop

@section('content')
    @if (getUser() && getUser('id') === $user->id)
        <?php $type = $active ? 'success' : 'adaptive'; ?>
        <a href="{{ route('downs.active-files', ['active' => 1]) }}" class="btn btn-{{ $type }} btn-sm">{{ __('loads.verified') }} <span class="badge bg-adaptive">{{ $activeCount }}</span></a>

        <?php $type = ! $active ? 'success' : 'adaptive'; ?>
        <a href="{{ route('downs.active-files', ['$active' => 0]) }}" class="btn btn-{{ $type }} btn-sm">{{ __('loads.pending') }} <span class="badge bg-adaptive">{{ $pendingCount }}</span></a>
        <hr>
    @endif

    @if ($downs->isNotEmpty())
        @foreach ($downs as $down)
            <div class="section mb-3 shadow">
                <div class="section-header d-flex align-items-center">
                    <div class="flex-grow-1">
                        <div class="section-title">
                            <i class="fa fa-file"></i>
                            <a href="{{ route('downs.view', ['id' => $down->id]) }}">{{ $down->title }}</a>
                        </div>
                    </div>

                    <div class="text-end js-rating">
                        <b>{{ formatNum($down->rating) }}</b>
                    </div>
                </div>

                <div class="section-content">
                    {{ __('loads.load') }}: <a href="/loads/{{ $down->category->id }}">{{ $down->category->name }}</a><br>
                    {{ __('main.downloads') }}: {{ $down->loads }}<br>
                    {{ __('main.author') }}: {{ $down->user->getProfile() }}
                    <small class="section-date text-muted fst-italic">{{ dateFixed($down->created_at) }}</small>
                </div>
            </div>
        @endforeach
    @else
        {{ showError(__('loads.empty_downs')) }}
    @endif

    {{ $downs->links() }}
@stop
