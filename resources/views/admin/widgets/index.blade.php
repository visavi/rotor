@extends('layout')

@section('title', __('index.widgets'))

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">{{ __('index.panel') }}</a></li>
            <li class="breadcrumb-item active">{{ __('index.widgets') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <div class="section mb-3 shadow">
        <div class="section-body">
            <p class="text-muted">{{ __('index.widgets_help') }}</p>

            <form action="{{ route('admin.widgets.update') }}" method="post">
                @csrf

                {{-- Порядок собирает Sortable; без JS отправится порядок, отрисованный сервером --}}
                <input type="hidden" name="order" id="widgets-order" value="{{ implode(',', array_keys($widgets)) }}">

                <div data-sortable data-sortable-target="#widgets-order">
                    @foreach ($widgets as $key => $widget)
                        <div class="d-flex align-items-center gap-2 border-bottom py-2" data-key="{{ $key }}">
                            <div class="form-check mb-0">
                                <input class="form-check-input" type="checkbox" name="widgets[]" value="{{ $key }}"
                                       id="widget-{{ $key }}" @checked($settings[$key] ?? true)>
                                <label class="form-check-label" for="widget-{{ $key }}">
                                    <span class="stat-tile-icon d-inline-flex align-middle me-1"
                                          style="background: {{ $widget['color'] }}1a; color: {{ $widget['color'] }}">
                                        <i class="{{ $widget['icon'] }}"></i>
                                    </span>
                                    {{ $widget['label'] }}
                                </label>
                            </div>

                            <span class="sortable-handle text-muted ms-auto" data-sortable-handle title="{{ __('index.widgets_drag') }}">
                                <i class="fas fa-grip-vertical"></i>
                            </span>
                        </div>
                    @endforeach
                </div>

                <button class="btn btn-primary mt-3">{{ __('main.save') }}</button>
            </form>
        </div>
    </div>
@stop
