@extends('layout')

@section('title')
    {{ trans('admin.banhists.view_history') }} {{ $user->login }}
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ trans('index.panel') }}</a></li>
            <li class="breadcrumb-item"><a href="/admin/banhists">{{ trans('index.ban_history') }}</a></li>
            <li class="breadcrumb-item active">{{ trans('admin.banhists.view_history') }} {{ $user->login }}</li>
        </ol>
    </nav>
@stop

@section('content')
    @if ($banhist->isNotEmpty())

        <form action="/admin/banhists/delete?user={{ $user->login }}&amp;page={{ $page->current }}" method="post">
            @csrf
            @foreach ($banhist as $data)
                <div class="b">

                    <div class="float-right">
                        <input type="checkbox" name="del[]" value="{{ $data->id }}">
                    </div>

                    <div class="img">
                        {!! $data->user->getAvatar() !!}
                        {!! $data->user->getOnline() !!}
                    </div>

                    <b>{!! $data->user->getProfile() !!}</b> ({{ dateFixed($data->created_at) }})
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

    @else
        {!! showError(trans('admin.banhists.empty_history')) !!}
    @endif
@stop
