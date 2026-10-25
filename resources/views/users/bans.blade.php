@extends('layout')

@section('title', __('index.banned'))

@section('content')
    @if ($banhist)
        <b class="text-danger">{{ __('users.reason_ban') }}: {{ $banhist->getReason() }}</b><br><br>

        @if (! $banhist->explain && setting('addbansend'))
            <div class="section-form mb-3 shadow">
                <form method="post" action="{{ route('ban.explain') }}">
                    @csrf
                    <div class="mb-3{{ hasError('msg') }}">
                        <label for="msg" class="form-label">{{ __('users.explanation') }}:</label>
                        <textarea class="form-control" id="msg" rows="5" name="msg" required>{{ old('msg') }}</textarea>
                        <div class="invalid-feedback">{{ textError('msg') }}</div>
                    </div>
                    <button class="btn btn-primary">{{ __('main.send') }}</button>
                </form>
            </div>

            {!! __('users.ban_text') !!}<br>
        @endif
    @endif

    {{ __('users.ending_ban') }}: <b>{{ formatTime((int) now()->diffInSeconds($user->timeban)) }}</b><br><br>
    {{ __('users.read_section') }} <b><a href="{{ route('rules') }}">{{ __('main.site_rules') }}</a></b><br><br>
    {!! __('users.ban_text2') !!}<br>
@stop
