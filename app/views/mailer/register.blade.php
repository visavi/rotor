@extends('mailer.layout')

@section('content')

    <table width="100%" cellpadding="0" cellspacing="0" style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0;">
        <tr style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0;">
            <td class="content-block" style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; vertical-align: top; margin: 0; padding: 0 0 20px;" valign="top">
                {!! $message !!}

                @if (Setting::get('regkeys') == 1)
                    Внимание!<br />
                    Для подтверждения регистрации необходимо в течение 24 часов ввести мастер-ключ!<br />
                    Ваш мастер-ключ: <b>{{ $activateKey }}</b><br />
                    Введите его после авторизации на сайте<br />
                    Или перейдите по прямой ссылке: <br /><br />
                    <b><a href="{{ $activateLink }}">{{ $activateLink }}</a></b><br /><br />

                    <a href="{{ $activateLink }}" class="btn-primary" style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; color: #FFF; text-decoration: none; line-height: 2; font-weight: bold; text-align: center; cursor: pointer; display: inline-block; border-radius: 5px; text-transform: capitalize; background: #5cb85c; margin: 0; padding: 8px 15px; border-color: #4cae4c; border-style: solid; border-width: 1px;">Активировать аккаунт</a><br /><br />

                    Если в течение 24 часов вы не подтвердите регистрацию, ваш аккаунт будет автоматически удален<br /><br />
                @endif


                @if (Setting::get('regkeys') == 2)
                    Внимание!<br />
                    Ваш аккаунт будет активирован только после проверки администрацией!<br />
                    Проверить статус активации вы сможете после авторизации на сайте<br /><br />
                @endif

                @if (Setting::get('regkeys') == 0)
                    <a href="{{ Setting::get('home') }}/login" class="btn-primary" style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; color: #FFF; text-decoration: none; line-height: 2; font-weight: bold; text-align: center; cursor: pointer; display: inline-block; border-radius: 5px; text-transform: capitalize; background: #5cb85c; margin: 0; padding: 8px 15px; border-color: #4cae4c; border-style: solid; border-width: 1px;">Войти на сайт</a><br /><br />
                @endif

                Надеемся вам понравится на нашем портале! <br />
                уважением администрация сайта <br />
                Если это письмо попало к вам по ошибке, то просто проигнорируйте его <br />
            </td>
        </tr>

    </table>

@stop
