@extends('layout')

@section('title', __('index.search_results'))

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/searchusers">{{ __('index.search_users') }}</a></li>
            <li class="breadcrumb-item active">{{ __('index.search_results') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    @if ($users->isNotEmpty())
        <div class="mb-3">
            @foreach ($users as $user)
                <div class="section mb-3 shadow">
                    <div class="user-avatar">
                        {{ $user->getAvatar() }}
                        {{ $user->getOnline() }}
                    </div>

                    <div class="section-user d-flex align-items-center">
                        <div class="flex-grow-1">
                            {{ $user->getProfile() }}

                            @if ($user->login !== $user->getName())
                                ({{ $user->login }})
                            @endif

                            <br>
                            <small class="font-italic">{{ $user->getStatus() }}</small>
                        </div>
                    </div>

                    <div class="section-body border-top">
                        {{ __('users.assets') }}: {{ plural($user->point, setting('scorename')) }}<br>
                        {{ __('users.reputation') }}: {{ formatNum($user->rating) }}<br>
                        {{ __('users.moneys') }}: {{ plural($user->money, setting('moneyname')) }}<br>
                        {{ __('main.registration_date') }}: {{ dateFixed($user->created_at, 'd.m.Y') }}
                    </div>
                </div>
            @endforeach
        </div>

        {{ $users->links() }}

        {{ __('main.total_found') }}: <b>{{ $users->total() }}</b><br>
    @else
        {{ showError(__('main.empty_found')) }}
    @endif
@stop
