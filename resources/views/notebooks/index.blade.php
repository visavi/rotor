@extends('layout')

@section('title')
    {{ trans('notebooks.title') }}
@stop

@section('content')

    <h1>{{ trans('notebooks.title') }}</h1>

    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/menu">{{ trans('common.menu') }}</a></li>
            <li class="breadcrumb-item active">{{ trans('notebooks.title') }}</li>
        </ol>
    </nav>

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

    <i class="fa fa-pencil-alt"></i> <a href="/notebooks/edit">{{ trans('notebooks.edit') }}</a><br>
@stop
