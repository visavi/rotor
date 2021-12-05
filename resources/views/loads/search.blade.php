@extends('layout')

@section('title', __('loads.search'))

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/loads">{{ __('index.loads') }}</a></li>
            <li class="breadcrumb-item active">{{ __('loads.search') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <div class="mb-3">
        <form method="get">
            <div class="input-group{{ hasError('find') }}">
                <input name="find" class="form-control" id="inputFind" minlength="3" maxlength="64" placeholder="{{ __('main.request') }}" value="{{ getInput('find', $find) }}" required>
                <button class="btn btn-primary">{{ __('main.search') }}</button>
            </div>
            <div class="invalid-feedback">{{ textError('find') }}</div>
            <span class="text-muted fst-italic"><?= __('main.request_requirements') ?></span>
        </form>
    </div>

    @if ($downs->isNotEmpty())
        <div class="mb-3">{{ __('main.total_found') }}: {{ $downs->total() }}</div>

        @foreach ($downs as $data)
            <div class="section mb-3 shadow">
                <div class="section-title">
                    <i class="fa fa-file"></i>
                    <a href="/downs/{{ $data->id }}">{{ $data->title }}</a>
                </div>

                <div class="section-content">
                    {{ $data->shortText() }}<br>

                    {{ __('loads.load') }}: <a href="/loads/{{ $data->category->id }}">{{ $data->category->name }}</a><br>
                    {{ __('main.rating') }}: {{ formatNum($data->rating) }}<br>
                    {{ __('main.author') }}: {{ $data->user->getProfile() }}
                    <small class="section-date text-muted fst-italic">{{ dateFixed($data->created_at) }}</small>
                </div>
            </div>
        @endforeach

        {{ $downs->links() }}
    @endif
@stop
