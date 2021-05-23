@extends('layout')

@section('title', __('index.antimat'))

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ __('index.panel') }}</a></li>
            <li class="breadcrumb-item active">{{ __('index.antimat') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    {!! __('admin.antimat.text') !!}<br>

    @if ($words->isNotEmpty())
        <div class="card mb-3">
            <h5 class="card-header">
                {{ __('admin.antimat.words') }}
            </h5>

            <div class="card-body">
                @foreach ($words as $data)
                    <a href="/admin/antimat/delete?id={{ $data->id }}&amp;token={{ csrf_token() }}">{{ $data->string }}</a>{{ $loop->last ? '' : ', ' }}
                @endforeach
            </div>

            <div class="card-footer">
                {{ __('admin.antimat.total_words') }}: <b>{{ $words->count() }}</b>

                @if (isAdmin('boss'))
                    <span class="float-end">
                        <i class="fa fa-trash-alt"></i> <a href="/admin/antimat/clear?token={{ csrf_token() }}" onclick="return confirm('{{ __('admin.antimat.confirm_clear') }}')">{{ __('main.clear') }}</a>
                    </span>
                @endif
            </div>
        </div>
    @else
        {{ showError(__('admin.antimat.empty_words')) }}
    @endif

    <form method="post">
        @csrf
        <div class="input-group">
            <span class="input-group-text"><i class="fa fa-pencil-alt"></i></span>
            <input type="text" class="form-control" name="word" placeholder="{{ __('admin.antimat.enter_word') }}" required>
            <button class="btn btn-primary">{{ __('main.add') }}</button>
        </div>
    </form>
@stop
