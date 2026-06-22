@extends('layout')

@section('title', __('admin.registries.title'))

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">{{ __('index.panel') }}</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.modules.index') }}">{{ __('index.modules') }}</a></li>
            <li class="breadcrumb-item active">{{ __('admin.registries.title') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    @include('admin/modules/_tabs')

    <div class="section mb-3 shadow">
        <div class="section-title">
            <i class="fas fa-plus"></i> {{ __('admin.registries.add_registry') }}
        </div>
        <div class="section-content">
            <form action="{{ route('admin.registries.store') }}" method="post" class="d-flex gap-2">
                @csrf
                <input type="url" class="form-control" name="url" placeholder="https://example.com/registry.json" required>
                <button class="btn btn-primary text-nowrap">{{ __('admin.registries.add') }}</button>
            </form>
        </div>
    </div>

    @if ($registries->isNotEmpty())
        @foreach ($registries as $registry)
            <div class="section mb-3 shadow">
                <div class="section-title d-flex align-items-center justify-content-between">
                    <div>
                        <i class="fas fa-database {{ $registry->active ? 'text-success' : 'text-muted' }}"></i>
                        <span class="fw-bold">{{ $registry->name ?: $registry->url }}</span>
                        @if ($registry->name)
                            <br><small class="text-muted">{{ $registry->url }}</small>
                        @endif
                    </div>
                    <div class="d-flex gap-2">
                        <form action="{{ route('admin.registries.refresh', $registry->id) }}" method="post">
                            @csrf
                            <button class="btn btn-sm btn-outline-secondary" title="{{ __('main.refresh') }}">
                                <i class="fas fa-sync-alt"></i>
                            </button>
                        </form>
                        <form action="{{ route('admin.registries.toggle', $registry->id) }}" method="post">
                            @csrf
                            <button class="btn btn-sm {{ $registry->active ? 'btn-outline-warning' : 'btn-outline-success' }}">
                                {{ $registry->active ? __('main.disable') : __('main.enable') }}
                            </button>
                        </form>
                        <form action="{{ route('admin.registries.destroy', $registry->id) }}" method="post"
                              onsubmit="return confirm('{{ __('admin.registries.confirm_delete') }}')">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger">
                                <i class="fas fa-times"></i>
                            </button>
                        </form>
                    </div>
                </div>
                <div class="section-content">
                    @if (! $registry->active)
                        <span class="badge bg-warning text-dark">{{ __('main.disabled') }}</span>
                    @else
                        <span class="badge bg-success">{{ __('main.active') }}</span>
                    @endif

                    @if ($registry->cached_at)
                        <span class="text-muted small ms-2">
                            {{ __('admin.registries.updated') }}: {{ dateFixed($registry->cached_at) }}
                        </span>
                        &nbsp;·&nbsp;
                        <span class="text-muted small">
                            {{ __('admin.registries.modules_count') }}: {{ count($registry->cached_data['modules'] ?? []) }}
                        </span>
                    @else
                        <span class="text-muted small ms-2">{{ __('admin.registries.not_fetched') }}</span>
                    @endif
                </div>
            </div>
        @endforeach
    @else
        {{ showError(__('admin.registries.empty')) }}
    @endif
@stop
