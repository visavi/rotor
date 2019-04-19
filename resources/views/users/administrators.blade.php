@extends('layout')

@section('title')
    {{ trans('users.admin_list') }}
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item active">{{ trans('users.admin_list') }}</li>
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
                    ({{ $user->getLevel() }})
                </div>
            @endforeach
        </div>

        {{ trans('users.total_administration') }}: <b>{{ $users->count() }}</b><br><br>

        @if (getUser())
            <h3>{{ trans('users.fast_mail') }}</h3>

            <div class="form">
                <form method="post" action="/messages/send">
                    <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

                    <div class="form-group">
                        <label for="user">{{ trans('users.choose_addressee') }}:</label>
                        <select class="form-control" id="user" name="user">
                            @foreach($users as $user)
                                <option value="{{ $user->login }}">{{ $user->login }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="msg">{{ trans('main.message') }}:</label>
                        <textarea class="form-control markItUp" maxlength="{{ setting('comment_length') }}" id="msg" rows="5" name="msg" required></textarea>
                        <span class="js-textarea-counter"></span>
                    </div>

                    @if (getUser('point') < setting('privatprotect'))
                        {!! view('app/_captcha') !!}
                    @endif

                    <button class="btn btn-primary">{{ trans('main.send') }}</button>
                </form>
            </div><br>
        @endif
    @else
        {!! showError(trans('users.empty_administration')) !!}
    @endif
@stop
