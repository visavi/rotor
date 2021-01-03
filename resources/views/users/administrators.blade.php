@extends('layout')

@section('title', __('index.admins'))

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item active">{{ __('index.admins') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    @if ($users->isNotEmpty())
        <div class="mb-3">
            @foreach ($users as $user)
                <div class="section mb-3 shadow">
                    <div class="user-avatar">
                        {!! $user->getAvatar() !!}
                        {!! $user->getOnline() !!}
                    </div>

                    <div class="section-user d-flex align-items-center">
                        <div class="flex-grow-1">
                            {!! $user->getProfile() !!}<br>
                            <small class="font-italic">{{ $user->getLevel() }}</small>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{ __('users.total_administration') }}: <b>{{ $users->count() }}</b><br><br>

        @if (getUser())
            <h5>{{ __('users.fast_mail') }}</h5>

            <div class="section-form shadow">
                <form method="post" action="/messages/send">
                    @csrf
                    <div class="form-group">
                        <label for="user">{{ __('users.choose_addressee') }}:</label>
                        <select class="form-control" id="user" name="user">
                            @foreach ($users as $user)
                                <option value="{{ $user->login }}">{{ $user->login }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="msg">{{ __('main.message') }}:</label>
                        <textarea class="form-control markItUp" maxlength="{{ setting('comment_length') }}" id="msg" rows="5" name="msg" required></textarea>
                        <span class="js-textarea-counter"></span>
                    </div>

                    @if (getUser('point') < setting('privatprotect'))
                        {!! view('app/_captcha') !!}
                    @endif

                    <button class="btn btn-primary">{{ __('main.send') }}</button>
                </form>
            </div>
        @endif
    @else
        {!! showError(__('users.empty_administration')) !!}
    @endif
@stop
