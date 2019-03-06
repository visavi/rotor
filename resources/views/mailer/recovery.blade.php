@extends('mailer.layout')

@section('header')
    <td class="alert alert-primary" style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 18px; vertical-align: top; color: #fff; font-weight: 600; border-radius: 5px 5px 0 0; background: #f0ad4e; margin: 0; padding: 20px;" valign="top">
        {{ $subject }}
    </td>
@stop

@section('content')
    <table width="100%" cellpadding="0" cellspacing="0" style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0;">
        <tr style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0;">
            <td class="content-block" style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; vertical-align: top; margin: 0; padding: 0 0 20px;" valign="top">
                {!! $message !!}
            </td>
        </tr>

        <tr style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0;">
            <td class="content-block" style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; vertical-align: top; margin: 0; padding: 0 0 20px;" valign="top">
                <a href="{{ $resetLink }}" class="btn-primary" style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; color: #FFF; text-decoration: none; line-height: 2; font-weight: bold; text-align: center; cursor: pointer; display: inline-block; border-radius: 5px; text-transform: capitalize; background: #d9534f; margin: 0; padding: 8px 15px; border-color: #ac2925; border-style: solid; border-width: 1px;">{{ trans('mailer.restore_password') }}</a>

                <p>
                    {{ trans('mailer.follow_link') }}:<br>
                    <b>{{ $resetLink }}</b>
                </p>
            </td>
        </tr>
    </table>
@stop
