@extends('layout')

@section('title', __('index.ban_history'))

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ __('index.panel') }}</a></li>
            <li class="breadcrumb-item active">{{ __('index.ban_history') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    @if ($records->isNotEmpty())
        <form action="/admin/banhists/delete?page={{ $records->currentPage() }}" method="post">
            @csrf
            @foreach ($records as $data)
                <div class="b">

                    <div class="float-right">
                        <a href="/admin/bans/change?user={{ $data->user->login }}" data-toggle="tooltip" title="{{ __('main.change') }}"><i class="fa fa-pencil-alt"></i></a>
                        <a href="/admin/banhists/view?user={{ $data->user->login }}" data-toggle="tooltip" title="{{ __('admin.banhists.history') }}"><i class="fa fa-history"></i></a>
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
                        {{ __('users.reason_ban') }}: {!! bbCode($data->reason) !!}<br>
                        {{ __('users.term') }}: {{ formatTime($data->term) }}<br>
                    @endif

                    {!! $data->getType() !!}: {!! $data->sendUser->getProfile() !!}<br>

                </div>
            @endforeach

            <div class="float-right">
                <button class="btn btn-sm btn-danger">{{ __('main.delete_selected') }}</button>
            </div>
        </form>
    @else
        {!! showError(__('admin.banhists.empty_history')) !!}
    @endif

    {{ $records->links() }}

    <div class="form mb-3">
        <form action="/admin/banhists/view" method="get">
            <b>{{ __('admin.banhists.search_user') }}:</b><br>
            <div class="form-inline">
                <div class="form-group{{ hasError('user') }}">
                    <input type="text" class="form-control" id="user" name="user" maxlength="20" value="{{ getInput('user') }}" placeholder="{{ __('main.user_login') }}" required>
                </div>

                <button class="btn btn-primary">{{ __('main.search') }}</button>
            </div>
            <div class="invalid-feedback">{{ textError('user') }}</div>
        </form>
    </div>
@stop
