@extends('mailer.layout')

@section('header')
    <td class="alert alert-success" style="font-family: 'Helvetica Neue',Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 18px; vertical-align: top; color: #fff; font-weight: 600; text-align: center; border-radius: 3px 3px 0 0; background-color: #299543; margin: 0; padding: 20px;" align="center" bgcolor="#299543" valign="top">
        {{ $subject }}
    </td>
@stop

@section('content')
    <table width="100%" cellpadding="0" cellspacing="0" style="font-family: 'Helvetica Neue',Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; margin: 0;">
        <tr style="font-family: 'Helvetica Neue',Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; margin: 0;">
            <td class="content-block" style="font-family: 'Helvetica Neue',Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; vertical-align: top; margin: 0; padding: 0 0 10px;" valign="top">
                <div style="font-weight: bold; padding: 0 0 10px;">Добро пожаловать, {{ $login }}</div>
                <div>Теперь вы зарегистрированный пользователь сайта <a href="{{ config('app.url') }}">{{ setting('title') }}</a>, сохраните ваш логин и пароль в надежном месте, они вам еще пригодятся.</div>
                <div style="padding: 0 0 10px;">Ваши данные для входа на сайт</div>

                <div style="font-weight: bold;">Логин: {{ $login }}</div>
                <div style="font-weight: bold;">Пароль: {{ $password }}</div>
            </td>
        </tr>
        <tr style="font-family: 'Helvetica Neue',Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; margin: 0;">
            <td class="content-block" style="font-family: 'Helvetica Neue',Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; vertical-align: top; margin: 0; padding: 0 0 10px;" valign="top">
                @if ($confirmUrl)
                    <p>
                        {{ __('mailer.activation_text1') }}<br>
                    </p>

                    <a href="{{ $confirmUrl }}" class="btn-success" style="font-family: 'Helvetica Neue',Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; color: #FFF; text-decoration: none; line-height: 2em; font-weight: bold; text-align: center; cursor: pointer; display: inline-block; border-radius: 5px; text-transform: capitalize; background-color: #5cb85c; margin: 0; border-color: #5cb85c; border-style: solid; border-width: 10px 20px;">{{ __('mailer.activate_account') }}</a>

                    <p>
                        {{ __('mailer.follow_link') }}:<br>
                        <b>{{ $confirmUrl }}</b><br><br>

                        {{ __('mailer.activation_text2') }}
                    </p>
                @else
                    <a href="{{ config('app.url') }}" class="btn-success" style="font-family: 'Helvetica Neue',Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; color: #FFF; text-decoration: none; line-height: 2em; font-weight: bold; text-align: center; cursor: pointer; display: inline-block; border-radius: 5px; text-transform: capitalize; background-color: #5cb85c; margin: 0; border-color: #5cb85c; border-style: solid; border-width: 10px 20px;">{{ __('mailer.enter_site') }}</a>
                @endif

                <p>
                    {{ __('mailer.registration_text1') }}<br>
                    {{ __('mailer.registration_text2') }}
                </p>
            </td>
        </tr>
    </table>
@stop
