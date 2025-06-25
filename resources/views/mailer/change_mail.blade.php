@extends('mailer.layout')

@section('content')
    <table width="100%" cellpadding="0" cellspacing="0" style="font-family: 'Helvetica Neue',Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; margin: 0;">
        <tr style="font-family: 'Helvetica Neue',Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; margin: 0;">
            <td class="content-block" style="font-family: 'Helvetica Neue',Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; vertical-align: top; margin: 0; padding: 0 0 20px;" valign="top">
                <div style="font-weight: bold; padding: 0 0 10px;">Здравствуйте, {{ $username }}</div>
                <div>Вами была произведена операция по изменению адреса электронной почты</div>

                <p>Для того, чтобы изменить email, необходимо подтвердить новый адрес почты</p>

                <a href="{{ $changeUrl }}" style="font-family: 'Helvetica Neue',Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; color: #FFF; text-decoration: none; line-height: 2em; font-weight: bold; text-align: center; cursor: pointer; display: inline-block; border-radius: 5px; text-transform: capitalize; background-color: #337AB7; margin: 0; border-color: #337AB7; border-style: solid; border-width: 10px 20px;">{{ __('mailer.change_email') }}</a>
                <p>
                    {{ __('mailer.follow_link') }}:<br>
                    <b>{{ $changeUrl }}</b>
                </p>

                <div>Ссылка будет действительной в течение 1 часа</div>
                <div>Для изменения адреса необходимо быть авторизованным на сайте</div>
                <div>Если это сообщение попало к вам по ошибке или вы не собираетесь менять email, то просто проигнорируйте данное письмо</div>
            </td>
        </tr>
    </table>
@stop
