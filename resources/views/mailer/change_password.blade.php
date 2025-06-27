@extends('mailer.layout')

@section('content')
    <table width="100%" cellpadding="0" cellspacing="0" style="font-family: 'Helvetica Neue',Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; margin: 0;">
        <tr style="font-family: 'Helvetica Neue',Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; margin: 0;">
            <td class="content-block" style="font-family: 'Helvetica Neue',Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; vertical-align: top; margin: 0; padding: 0 0 20px;" valign="top">
                <div style="font-weight: bold; padding: 0 0 10px;">Здравствуйте, {{ $username }}</div>
                <div>Вами была произведена операция по изменению пароля</div>
                <div style="padding: 0 0 10px;">Сохраните его в надежном месте</div>

                <div style="font-weight: bold;">Ваш новый пароль: {{ $password }}</div>
            </td>
        </tr>
    </table>
@stop
