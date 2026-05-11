@extends('layout')

@section('title', __('index.css_js_editor'))

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">{{ __('index.panel') }}</a></li>
            <li class="breadcrumb-item active">{{ __('index.css_js_editor') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <div class="section-form mb-3 shadow">
        <ul class="nav nav-tabs mb-3">
            <li class="nav-item">
                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-css" type="button">
                    <i class="fas fa-palette"></i> CSS
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-js" type="button">
                    <i class="fas fa-code"></i> JavaScript
                </button>
            </li>
        </ul>

        <form action="{{ route('admin.editor.save') }}" method="post">
            @csrf

            <div class="tab-content">
                <div class="tab-pane fade show active" id="tab-css">
                    <div class="mb-1 text-muted small">{{ __('admin.editor.css_hint') }}</div>
                    <textarea class="form-control font-monospace" name="css" rows="25" spellcheck="false">{{ $css }}</textarea>
                </div>
                <div class="tab-pane fade" id="tab-js">
                    <div class="mb-1 text-muted small">{{ __('admin.editor.js_hint') }}</div>
                    <textarea class="form-control font-monospace" name="js" rows="25" spellcheck="false">{{ $js }}</textarea>
                </div>
            </div>

            <div class="mt-3">
                <button class="btn btn-primary">{{ __('main.save') }}</button>
            </div>
        </form>
    </div>
@stop

@push('scripts')
    <script>
        document.querySelectorAll('textarea.font-monospace').forEach(function (textarea) {
            textarea.addEventListener('keydown', function (e) {
                if (e.key === 'Tab') {
                    e.preventDefault();
                    const start = this.selectionStart;
                    const end = this.selectionEnd;
                    this.value = this.value.substring(0, start) + '    ' + this.value.substring(end);
                    this.selectionStart = this.selectionEnd = start + 4;
                }
            });
        });
    </script>
@endpush
