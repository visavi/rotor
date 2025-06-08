@extends('layout')

@section('title', __('admin.banhists.view_history') . ' ' . $user->getName())

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">{{ __('index.panel') }}</a></li>
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
                <div class="section mb-3 shadow">
                    <div class="user-avatar">
                        {{ $data->user->getAvatar() }}
                        {{ $data->user->getOnline() }}
                    </div>

                    <div class="section-user d-flex align-items-center">
                        <div class="flex-grow-1">
                            {{ $data->user->getProfile() }}
                            <small class="section-date text-muted fst-italic">{{ dateFixed($data->created_at) }}</small>
                        </div>
                        <div class="text-end">
                            <input type="checkbox" class="form-check-input" name="del[]" value="{{ $data->id }}">
                        </div>
                    </div>

                    <div class="section-body border-top">
                        @if ($data->type !== 'unban')
                            {{ __('users.reason_ban') }}: {{ bbCode($data->reason) }}<br>
                            {{ __('users.term') }}: {{ formatTime($data->term) }}<br>
                        @endif

                        {{ $data->getType() }}: {{ $data->sendUser->getProfile() }}<br>
                    </div>
                </div>
            @endforeach

            <div class="clearfix mb-3">
                <button class="btn btn-sm btn-danger float-end">{{ __('main.delete_selected') }}</button>
            </div>
        </form>
    @else
        {{ showError(__('admin.banhists.empty_history')) }}
    @endif

    {{ $banhist->links() }}
@stop
