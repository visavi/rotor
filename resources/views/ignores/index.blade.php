@extends('layout')

@section('title')
    {{ trans('ignores.title') }}
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/menu">{{ trans('main.menu') }}</a></li>
            <li class="breadcrumb-item active">{{ trans('ignores.title') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    @if ($ignores->isNotEmpty())

        <form action="/ignores/delete?page={{ $page->current }}" method="post">
            <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

            @foreach ($ignores as $data)
                <div class="b">

                    <div class="float-right">
                        <a href="/messages/talk/{{ $data->ignoring->login }}" data-toggle="tooltip" title="{{ trans('ignores.write') }}"><i class="fa fa-reply text-muted"></i></a>
                        <a href="/ignores/note/{{ $data->id }}" data-toggle="tooltip" title="{{ trans('ignores.note') }}"><i class="fa fa-sticky-note text-muted"></i></a>
                        <input type="checkbox" name="del[]" value="{{ $data->id }}">
                    </div>

                    <div class="img">
                        {!! $data->ignoring->getAvatar() !!}
                        {!! $data->ignoring->getOnline() !!}
                    </div>

                    <b>{!! $data->ignoring->getProfile() !!}</b> <small>({{ dateFixed($data->created_at) }})</small><br>
                    {!! $data->ignoring->getStatus() !!}
                </div>

                <div>
                    @if ($data->text)
                        {{ trans('ignores.note') }}: {!! bbCode($data->text) !!}<br>
                    @endif
                </div>
            @endforeach

            <div class="float-right">
                <button class="btn btn-sm btn-danger">{{ trans('main.delete_selected') }}</button>
            </div>
        </form>

        {!! pagination($page) !!}

        {{ trans('ignores.total') }}: <b>{{ $page->total }}</b><br>
    @else
        {!! showError(trans('ignores.empty_list')) !!}
    @endif

    <div class="form my-3">
        <form method="post">
            <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">
            <div class="input-group{{ hasError('user') }}">
                <div class="input-group-prepend">
                    <span class="input-group-text"><i class="fa fa-pencil-alt"></i></span>
                </div>

                <input type="text" class="form-control" id="user" name="user" maxlength="20" value="{{ getInput('user', $login) }}" placeholder="{{ trans('ignores.user_login') }}" required>

                <span class="input-group-btn">
                    <button class="btn btn-primary">{{ trans('ignores.add') }}</button>
                </span>
            </div>
            {!! textError('user') !!}
        </form>
    </div>

    <i class="fa fa-users"></i> <a href="/contacts">{{ trans('app.contact') }}</a><br>
    <i class="fa fa-envelope"></i> <a href="/messages">{{ trans('app.message') }}</a><br>
@stop
