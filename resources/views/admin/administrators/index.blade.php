@extends('layout')

@section('title', __('index.admins'))

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ __('index.panel') }}</a></li>
            <li class="breadcrumb-item active">{{ __('index.admins') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    @if ($users->isNotEmpty())

        @foreach ($users as $user)
            <div class="section mb-3 shadow">
                <div class="user-avatar">
                    {!! $user->getAvatar() !!}
                    {!! $user->getOnline() !!}
                </div>

                {!! $user->getProfile() !!}
                ({{ $user->getLevel() }})<br>

                @if (isAdmin('boss'))
                    <i class="fa fa-pencil-alt"></i> <a href="/admin/users/edit?user={{ $user->login }}">{{ __('main.change') }}</a><br>
                @endif
            </div>
        @endforeach

        {{ __('users.total_administration') }}: <b>{{ $users->count() }}</b><br><br>
    @else
        {!! showError( __('users.empty_administration')) !!}
    @endif
@stop
