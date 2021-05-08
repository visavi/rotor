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
                <div class="section mb-3 shadow">
                    <div class="user-avatar">
                        {{ $data->user->getAvatar() }}
                        {{ $data->user->getOnline() }}
                    </div>

                    <div class="section-user d-flex align-items-center">
                        <div class="flex-grow-1">
                            {{ $data->user->getProfile() }}
                            <small class="section-date text-muted font-italic">{{ dateFixed($data->created_at) }}</small>
                        </div>

                        <div class="text-end">
                            <a href="/admin/bans/change?user={{ $data->user->login }}" data-bs-toggle="tooltip" title="{{ __('main.change') }}"><i class="fa fa-pencil-alt"></i></a>
                            <a href="/admin/banhists/view?user={{ $data->user->login }}" data-bs-toggle="tooltip" title="{{ __('admin.banhists.history') }}"><i class="fa fa-history"></i></a>
                            <input type="checkbox" name="del[]" value="{{ $data->id }}">
                        </div>
                    </div>

                    <div class="section-body border-top">
                        @if ($data->type !== 'unban')
                            {{ __('users.reason_ban') }}: {{ bbCode($data->reason) }}<br>
                            {{ __('users.term') }}: {{ formatTime($data->term) }}<br>
                        @endif

                        {{ $data->getType() }}: {{ $data->sendUser->getProfile() }}
                    </div>
                </div>
            @endforeach

            <div class="float-end">
                <button class="btn btn-sm btn-danger">{{ __('main.delete_selected') }}</button>
            </div>
        </form>
    @else
        {{ showError(__('admin.banhists.empty_history')) }}
    @endif

    {{ $records->links() }}

    <div class="section-form mb-3 shadow">
        <form action="/admin/banhists/view" method="get">
            <label for="user" class="form-label">{{ __('admin.banhists.search_user') }}:</label>
            <div class="input-group{{ hasError('user') }}">
                <input type="text" class="form-control" id="user" name="user" maxlength="20" value="{{ getInput('user') }}" placeholder="{{ __('main.user_login') }}" required>
                <div class="input-group-append">
                    <button class="btn btn-primary">{{ __('main.search') }}</button>
                </div>
            </div>
            <div class="invalid-feedback">{{ textError('user') }}</div>
        </form>
    </div>
@stop
