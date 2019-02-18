@extends('layout')

@section('title')
    {{ trans('notebooks.title') }}
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/menu">{{ trans('main.menu') }}</a></li>
            <li class="breadcrumb-item active">{{ trans('notebooks.title') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    {{ trans('notebooks.info') }}<br><br>

    @if ($note->text)
        <div>{{ trans('notebooks.subtitle') }}:<br>
            {!! bbCode($note->text) !!}
        </div>
        <br>

        {{ trans('notebooks.last_edited') }}: {{ dateFixed($note->created_at) }}<br><br>
    @else
        {!! showError(trans('notebooks.empty_note')) !!}
    @endif

    <i class="fa fa-pencil-alt"></i> <a href="/notebooks/edit">{{ trans('main.edit') }}</a><br>
@stop
