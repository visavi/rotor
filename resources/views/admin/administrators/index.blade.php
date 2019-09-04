@extends('layout')

@section('title')
    {{ __('index.admins') }}
@stop

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

        <div class="mb-3">
            @foreach($users as $user)
                <div  class="text-truncate bg-light my-1">
                    <div class="img">
                        {!! $user->getAvatar() !!}
                        {!! $user->getOnline() !!}
                    </div>

                    <b>{!! $user->getProfile() !!}</b>
                    ({{ $user->getLevel() }})<br>

                    @if (isAdmin('boss'))
                        <i class="fa fa-pencil-alt"></i> <a href="/admin/users/edit?user={{ $user->login }}">{{ __('main.change') }}</a><br>
                    @endif
                </div>
            @endforeach
        </div>

        {{ __('users.total_administration') }}: <b>{{ $users->count() }}</b><br><br>
    @else
        {!! showError( __('users.empty_administration')) !!}
    @endif
@stop
