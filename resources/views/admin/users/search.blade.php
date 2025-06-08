@extends('layout')

@section('title', __('index.search_users'))

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">{{ __('index.panel') }}</a></li>
            <li class="breadcrumb-item"><a href="/admin/users">{{ __('index.users') }}</a></li>
            <li class="breadcrumb-item active">{{ __('index.search_users') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    @if ($users->isNotEmpty())
        @foreach ($users as $user)
            <div class="section mb-3 shadow">
                <div class="user-avatar">
                    {{ $user->getAvatar() }}
                    {{ $user->getOnline() }}
                </div>

                <div class="section-content">
                    <b><a href="/admin/users/edit?user={{ $user->login }}">{{ $user->getName() }}</a></b>
                    ({{ plural($user->point, setting('scorename')) }})<br>

                    {{ __('users.email') }}: {{ $user->email }}<br>
                    {{ __('users.registered') }}: {{ dateFixed($user->created_at, 'd.m.Y') }}
                </div>
            </div>
        @endforeach

        {{ $users->links() }}

        <div class="mb-3">
            {{ __('main.total_found') }}: {{ $users->total() }}
        </div>
    @else
        {{ showError(__('main.empty_found')) }}
    @endif
@stop
