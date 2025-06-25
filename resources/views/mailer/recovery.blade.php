@extends('mailer.layout')

@section('header')
    <td class="alert alert-warning" style="font-family: 'Helvetica Neue',Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 18px; vertical-align: top; color: #fff; font-weight: 600; text-align: center; border-radius: 3px 3px 0 0; background-color: #FF9F00; margin: 0; padding: 20px;" align="center" bgcolor="#FF9F00" valign="top">
        {{ $subject }}
    </td>
@stop

@section('content')
    <table width="100%" cellpadding="0" cellspacing="0" style="font-family: 'Helvetica Neue',Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; margin: 0;">
        <tr style="font-family: 'Helvetica Neue',Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; margin: 0;">
            <td class="content-block" style="font-family: 'Helvetica Neue',Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; vertical-align: top; margin: 0; padding: 0 0 10px;" valign="top">
                <div style="font-weight: bold; padding: 0 0 10px;">Здравствуйте, {{ $username }}</div>

                <div style="padding: 0 0 10px;">Вами была произведена операция по восстановлению пароля на сайте <a href="{{ config('app.url') }}">{{ setting('title') }}</a></div>

                <div>Для того чтобы восстановить пароль, вам необходимо нажать на кнопку восстановления</div>
                <div>Если это письмо попало к вам по ошибке или вы не собираетесь восстанавливать пароль, то просто проигнорируйте его</div>
            </td>
        </tr>
        <tr style="font-family: 'Helvetica Neue',Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; margin: 0;">
            <td class="content-block" style="font-family: 'Helvetica Neue',Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; vertical-align: top; margin: 0; padding: 0 0 10px;" valign="top">
                <a href="{{ $resetUrl }}" style="font-family: 'Helvetica Neue',Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; color: #FFF; text-decoration: none; line-height: 2em; font-weight: bold; text-align: center; cursor: pointer; display: inline-block; border-radius: 5px; text-transform: capitalize; background-color: #d9534f; margin: 0; border-color: #d9534f; border-style: solid; border-width: 10px 20px;">{{ __('mailer.restore_password') }}</a>
                <p>
                    {{ __('mailer.follow_link') }}:<br>
                    <b>{{ $resetUrl }}</b>
                </p>
            </td>
        </tr>
    </table>
@stop
