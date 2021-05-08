@extends('layout')

@section('title', __('index.notebook'))

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/menu">{{ __('main.menu') }}</a></li>
            <li class="breadcrumb-item active">{{ __('index.notebook') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <div class="mb-3">
        {{ __('notebooks.info') }}
    </div>

    @if ($note->text)
        <div class="mb-3">
            {{ __('notebooks.subtitle') }}:<br>
            {{ bbCode($note->text) }}
        </div>

        <p class="text-muted fst-italic">
            {{ __('notebooks.last_edited') }}: {{ dateFixed($note->created_at) }}
        </p>
    @else
        {{ showError(__('notebooks.empty_note')) }}
    @endif

    <i class="fa fa-pencil-alt"></i> <a href="/notebooks/edit">{{ __('main.edit') }}</a><br>
@stop
