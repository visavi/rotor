@extends('layout')

@section('title')
    {{ trans('index.banned') }}
@stop

@section('content')
    @if ($banhist)
        <b><span style="color:#ff0000">{{ trans('users.reason_ban') }}: {!! bbCode($banhist->reason) !!}</span></b><br><br>

        @if ($banhist->explain && setting('addbansend'))
            <div class="form">
                <form method="post" action="/ban">

                  <div class="form-group{{ hasError('msg') }}">
                      <label for="msg">{{ trans('users.explanation') }}:</label>
                      <textarea class="form-control markItUp" id="msg" rows="5" name="msg" required>{{ getInput('msg') }}</textarea>
                      <div class="invalid-feedback">{{ textError('msg') }}</div>
                  </div>

                    <button class="btn btn-primary">{{ trans('main.send') }}</button>
                </form>
            </div><br>

            {!! trans('users.ban_text') !!}<br>
        @endif
    @endif

    {{ trans('users.ending_ban') }}: <b>{{ formatTime($user->timeban - SITETIME) }}</b><br><br>
    {{ trans('users.read_section') }} <b><a href="/rules">{{ trans('main.site_rules') }}</a></b><br><br>
    {!! trans('users.ban_text2') !!}<br>
@stop
