@extends('layout')

@section('title', __('admin.banhists.view_history') . ' ' . $user->getName())

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ __('index.panel') }}</a></li>
            <li class="breadcrumb-item"><a href="/admin/banhists">{{ __('index.ban_history') }}</a></li>
            <li class="breadcrumb-item active">{{ __('admin.banhists.view_history') }} {{ $user->getName() }}</li>
        </ol>
    </nav>
@stop

@section('content')
    @if ($banhist->isNotEmpty())
        <form action="/admin/banhists/delete?user={{ $user->login }}&amp;page={{ $banhist->currentPage() }}" method="post">
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

                    {!! $data->user->getProfile() !!} ({{ dateFixed($data->created_at) }})
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

    {{ $banhist->links() }}
@stop
