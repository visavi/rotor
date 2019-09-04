@extends('layout')

@section('title')
    {{ __('index.notebook') }}
@stop

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
    {{ __('notebooks.info') }}<br><br>

    @if ($note->text)
        <div>{{ __('notebooks.subtitle') }}:<br>
            {!! bbCode($note->text) !!}
        </div>
        <br>

        {{ __('notebooks.last_edited') }}: {{ dateFixed($note->created_at) }}<br><br>
    @else
        {!! showError(__('notebooks.empty_note')) !!}
    @endif

    <i class="fa fa-pencil-alt"></i> <a href="/notebooks/edit">{{ __('main.edit') }}</a><br>
@stop
