@props(['subject' => '', 'preheader' => null])

@php
    $font = "-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif";
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="x-apple-disable-message-reformatting">
    <meta name="color-scheme" content="light dark">
    <meta name="supported-color-schemes" content="light dark">
    <title>{{ $subject }}</title>
    <style>
        :root { color-scheme: light dark; supported-color-schemes: light dark; }

        @media only screen and (max-width: 620px) {
            .container { width: 100% !important; }
            .gutter { padding-left: 20px !important; padding-right: 20px !important; }
            .btn { display: block !important; text-align: center !important; }
        }

        @media (prefers-color-scheme: dark) {
            body, .bg { background-color: #16191d !important; }
            .masthead { background-color: #1c2026 !important; border-bottom-color: #2c333a !important; }
            .text, .heading { color: #e6e9ed !important; }
            .muted { color: #9aa4ae !important; }
            .panel { background-color: #21262c !important; }
            .divider { border-top-color: #2c333a !important; }
            a { color: #6ea8fe !important; }
            a.btn { color: #ffffff !important; }
        }
    </style>
</head>
<body class="bg" style="margin: 0; padding: 0; background-color: #ffffff; -webkit-text-size-adjust: none;">

@if ($preheader)
    <div style="display: none; font-size: 1px; line-height: 1px; max-height: 0; max-width: 0; opacity: 0; overflow: hidden; color: transparent;">
        {{ $preheader }}&#847;&zwnj;&nbsp;&#847;&zwnj;&nbsp;&#847;&zwnj;&nbsp;&#847;&zwnj;&nbsp;&#847;&zwnj;&nbsp;&#847;&zwnj;&nbsp;&#847;&zwnj;&nbsp;&#847;&zwnj;&nbsp;&#847;&zwnj;&nbsp;&#847;&zwnj;&nbsp;
    </div>
@endif

<table class="bg" role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color: #ffffff;">
    <tr>
        <td align="center" style="padding: 24px 0 32px;">
            <table class="container" role="presentation" width="600" cellpadding="0" cellspacing="0" border="0" style="width: 600px;">

                <tr>
                    <td class="masthead gutter" style="padding: 20px; background-color: #f6f7f9; border-bottom: 2px solid #0d6efd; border-radius: 10px 10px 0 0;">
                        <a href="{{ config('app.url') }}" style="font-family: {{ $font }}; font-size: 20px; font-weight: 700; letter-spacing: -0.3px; color: #0d6efd; text-decoration: none;">{{ setting('title') }}</a>
                    </td>
                </tr>

                <tr>
                    <td class="gutter text" style="padding: 28px 0; font-family: {{ $font }}; font-size: 15px; line-height: 1.6; color: #21262c;">
                        {{ $slot }}

                        @isset($subcopy)
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin: 28px 0 0;">
                                <tr>
                                    <td class="divider" style="border-top: 1px solid #e9ecef; font-size: 0; line-height: 0;">&nbsp;</td>
                                </tr>
                                <tr>
                                    <td class="muted" style="padding: 20px 0 0; font-family: {{ $font }}; font-size: 13px; line-height: 1.55; color: #636c76;">
                                        {{ $subcopy }}
                                    </td>
                                </tr>
                            </table>
                        @endisset
                    </td>
                </tr>

                <tr>
                    <td class="gutter divider" style="border-top: 1px solid #e9ecef; padding: 16px 0 28px;">
                        <a href="{{ config('app.url') }}" class="muted" style="font-family: {{ $font }}; font-size: 12px; line-height: 1.5; color: #8b949e; text-decoration: underline;">{{ setting('copy') }}</a>
                    </td>
                </tr>

            </table>
        </td>
    </tr>
</table>
</body>
</html>
