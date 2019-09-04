@extends('layout')

@section('title')
    {{ __('index.antimat') }}
@stop

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

        <div class="card">
            <h2 class="card-header">
                {{ __('admin.antimat.words') }}
            </h2>

            <div class="card-body">
                @foreach ($words as $data)
                    <a href="/admin/antimat/delete?id={{ $data->id }}&amp;token={{ $_SESSION['token'] }}">{{ $data->string }}</a>{{ $loop->last ? '' : ', ' }}
                @endforeach
            </div>

            <div class="card-footer">
                {{ __('admin.antimat.total_words') }}: <b>{{ $words->count() }}</b>

                @if (isAdmin('boss'))
                    <span class="float-right">
                        <i class="fa fa-trash-alt"></i> <a href="/admin/antimat/clear?token={{ $_SESSION['token'] }}" onclick="return confirm('{{ __('admin.antimat.confirm_clear') }}')">{{ __('main.clear') }}</a>
                    </span>
                @endif
            </div>
        </div>
        <br>

    @else
        {!! showError(__('admin.antimat.empty_words')) !!}
    @endif

    <form method="post">
        @csrf
        <div class="input-group">
            <div class="input-group-prepend">
                <span class="input-group-text"><i class="fa fa-pencil-alt"></i></span>
            </div>

            <input type="text" class="form-control" name="word" placeholder="{{ __('admin.antimat.enter_word') }}" required>

            <span class="input-group-btn">
                <button class="btn btn-primary">{{ __('main.add') }}</button>
            </span>
        </div>
    </form>
@stop
