@extends('mailer.layout')

@section('content')
    <table width="100%" cellpadding="0" cellspacing="0" style="font-family: 'Helvetica Neue',Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; margin: 0;">
        <tr style="font-family: 'Helvetica Neue',Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; margin: 0;">
            <td class="content-block" style="font-family: 'Helvetica Neue',Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; vertical-align: top; margin: 0; padding: 0 0 20px;" valign="top">
                <div style="font-weight: bold; padding: 0 0 10px;">Здравствуйте, {{ $username }}</div>
                <div style="padding: 0 0 10px;">Ваши новые данные для входа на сайт <a href="{{ config('app.url') }}">{{ setting('title') }}</a></div>

                <div style="font-weight: bold;">Логин: {{ $login }}</div>
                <div style="font-weight: bold;">Пароль: {{ $password }}</div>

                <div style="padding: 10px 0 0;">Запомните и постарайтесь больше не забывать данные</div>
                <div>Пароль вы сможете поменять в своем профиле</div>
                <div>Всего наилучшего!</div>
            </td>
        </tr>
    </table>
@stop
