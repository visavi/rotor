@extends('layout')

@section('title')
    {{ trans('index.ban_history') }}
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ trans('index.panel') }}</a></li>
            <li class="breadcrumb-item active">{{ trans('index.ban_history') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    @if ($records->isNotEmpty())

    <form action="/admin/banhists/delete?page={{ $page->current }}" method="post">
        @csrf
        @foreach ($records as $data)
            <div class="b">

                <div class="float-right">
                    <a href="/admin/bans/change?user={{ $data->user->login }}" data-toggle="tooltip" title="{{ trans('main.change') }}"><i class="fa fa-pencil-alt"></i></a>
                    <a href="/admin/banhists/view?user={{ $data->user->login }}" data-toggle="tooltip" title="{{ trans('admin.banhists.history') }}"><i class="fa fa-history"></i></a>
                    <input type="checkbox" name="del[]" value="{{ $data->id }}">
                </div>

                <div class="img">
                    {!! $data->user->getAvatar() !!}
                    {!! $data->user->getOnline() !!}
                </div>

                <b>{!! $data->user->getProfile() !!}</b>

                <small>({{ dateFixed($data->created_at) }})</small><br>
            </div>
            <div>
                @if ($data->type !== 'unban')
                    {{ trans('users.reason_ban') }}: {!! bbCode($data->reason) !!}<br>
                    {{ trans('users.term') }}: {{ formatTime($data->term) }}<br>
                @endif

                {!! $data->getType() !!}: {!! $data->sendUser->getProfile() !!}<br>

            </div>
        @endforeach

        <div class="float-right">
            <button class="btn btn-sm btn-danger">{{ trans('main.delete_selected') }}</button>
        </div>
    </form>

    {!! pagination($page) !!}

    <div class="form mb-3">
        <form action="/admin/banhists/view" method="get">
            <b>{{ trans('admin.banhists.search_user') }}:</b><br>
            <div class="form-inline">
                <div class="form-group{{ hasError('user') }}">
                    <input type="text" class="form-control" id="user" name="user" maxlength="20" value="{{ getInput('user') }}" placeholder="{{ trans('main.user_login') }}" required>
                </div>

                <button class="btn btn-primary">{{ trans('main.search') }}</button>
            </div>
            <div class="invalid-feedback">{{ textError('user') }}</div>
        </form>
    </div>

    @else
        {!! showError(trans('admin.banhists.empty_history')) !!}
    @endif
@stop
