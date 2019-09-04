@extends('mailer.layout')

@section('content')
    <table width="100%" cellpadding="0" cellspacing="0" style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0;">
        <tr style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0;">
            <td class="content-block" style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; vertical-align: top; margin: 0; padding: 0 0 20px;" valign="top">
                {!! $message !!}

                @if (setting('regkeys'))
                    {{ __('mailer.activation_text1') }}<br>
                    {{ __('mailer.activation_code') }}: <b>{{ $activateKey }}</b><br>
                    {{ __('mailer.activation_text2') }}<br><br>

                    <a href="{{ $activateLink }}" class="btn-primary" style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; color: #FFF; text-decoration: none; line-height: 2; font-weight: bold; text-align: center; cursor: pointer; display: inline-block; border-radius: 5px; text-transform: capitalize; background: #5cb85c; margin: 0; padding: 8px 15px; border: 1px solid #4cae4c;">{{ __('mailer.activate_account') }}</a><br><br>

                    {{ __('mailer.follow_link') }}: <br>
                    {{ $activateLink }}<br><br>

                    {{ __('mailer.activation_text3') }}<br>
                @else
                    <a href="{{ siteUrl(true) }}/login" class="btn-primary" style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; color: #FFF; text-decoration: none; line-height: 2; font-weight: bold; text-align: center; cursor: pointer; display: inline-block; border-radius: 5px; text-transform: capitalize; background: #5cb85c; margin: 0; padding: 8px 15px; border: 1px solid #4cae4c;">{{ __('mailer.enter_site') }}</a><br><br>
                @endif

                {{ __('mailer.registration_text1') }}<br>
                {{ __('mailer.registration_text2') }}<br>
            </td>
        </tr>
    </table>
@stop
