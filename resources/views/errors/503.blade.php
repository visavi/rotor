<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>503 — {{ __('errors.service_unavailable') }}</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #0f172a;
            color: #e2e8f0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .bg {
            position: fixed;
            inset: 0;
            background: radial-gradient(ellipse at 20% 50%, rgba(99,102,241,.15) 0%, transparent 60%),
                        radial-gradient(ellipse at 80% 20%, rgba(168,85,247,.1) 0%, transparent 50%);
        }

        .wrap {
            position: relative;
            text-align: center;
            padding: 2rem;
            max-width: 480px;
        }

        .gear-wrap {
            position: relative;
            width: 120px;
            height: 120px;
            margin: 0 auto 2rem;
        }

        .gear {
            position: absolute;
            fill: none;
            stroke: #6366f1;
            stroke-width: 2;
        }

        .gear-big {
            width: 90px; height: 90px;
            top: 15px; left: 0;
            animation: spin 8s linear infinite;
            stroke: #6366f1;
        }

        .gear-small {
            width: 50px; height: 50px;
            top: 0; right: 0;
            animation: spin 8s linear infinite reverse;
            stroke: #a855f7;
        }

        @keyframes spin {
            from { transform: rotate(0deg); }
            to   { transform: rotate(360deg); }
        }

        .code {
            font-size: 5rem;
            font-weight: 800;
            line-height: 1;
            background: linear-gradient(135deg, #6366f1, #a855f7);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: .5rem;
        }

        h1 {
            font-size: 1.4rem;
            font-weight: 600;
            color: #f1f5f9;
            margin-bottom: .75rem;
        }

        p {
            color: #94a3b8;
            line-height: 1.6;
            margin-bottom: 2rem;
        }

        .btn {
            display: inline-block;
            padding: .65rem 1.6rem;
            background: linear-gradient(135deg, #6366f1, #a855f7);
            color: #fff;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 500;
            font-size: .95rem;
            transition: opacity .2s;
        }

        .btn:hover { opacity: .85; }

        .dots {
            display: inline-flex;
            gap: 6px;
            margin-bottom: 1.5rem;
        }

        .dot {
            width: 8px; height: 8px;
            border-radius: 50%;
            background: #6366f1;
            animation: bounce 1.4s ease-in-out infinite;
        }

        .dot:nth-child(2) { animation-delay: .2s; background: #818cf8; }
        .dot:nth-child(3) { animation-delay: .4s; background: #a855f7; }

        @keyframes bounce {
            0%, 80%, 100% { transform: translateY(0); }
            40%           { transform: translateY(-10px); }
        }
    </style>
</head>
<body>
    <div class="bg"></div>
    <div class="wrap">
        <div class="gear-wrap">
            <svg class="gear gear-big" viewBox="0 0 100 100">
                <path d="M43.3 5.8l-3.1 9.5a35 35 0 0 0-8.5 3.5l-9-4.7-9.2 9.2 4.7 9a35 35 0 0 0-3.5 8.5L5.8 44v13l9.5 3.1a35 35 0 0 0 3.5 8.5l-4.7 9 9.2 9.2 9-4.7a35 35 0 0 0 8.5 3.5l3.1 9.6h13l3.1-9.5a35 35 0 0 0 8.5-3.5l9 4.7 9.2-9.2-4.7-9a35 35 0 0 0 3.5-8.5l9.5-3.1V44l-9.5-3.1a35 35 0 0 0-3.5-8.5l4.7-9-9.2-9.2-9 4.7a35 35 0 0 0-8.5-3.5L56.2 5.8z"/>
                <circle cx="50" cy="50" r="15"/>
            </svg>
            <svg class="gear gear-small" viewBox="0 0 100 100">
                <path d="M43.3 5.8l-3.1 9.5a35 35 0 0 0-8.5 3.5l-9-4.7-9.2 9.2 4.7 9a35 35 0 0 0-3.5 8.5L5.8 44v13l9.5 3.1a35 35 0 0 0 3.5 8.5l-4.7 9 9.2 9.2 9-4.7a35 35 0 0 0 8.5 3.5l3.1 9.6h13l3.1-9.5a35 35 0 0 0 8.5-3.5l9 4.7 9.2-9.2-4.7-9a35 35 0 0 0 3.5-8.5l9.5-3.1V44l-9.5-3.1a35 35 0 0 0-3.5-8.5l4.7-9-9.2-9.2-9 4.7a35 35 0 0 0-8.5-3.5L56.2 5.8z"/>
                <circle cx="50" cy="50" r="15"/>
            </svg>
        </div>

        <div class="code">503</div>
        <h1>{{ __('errors.service_unavailable') }}</h1>
        <p>Сайт временно недоступен в связи с техническими работами.<br>Скоро всё заработает!</p>

        <div class="dots">
            <span class="dot"></span>
            <span class="dot"></span>
            <span class="dot"></span>
        </div>

        <br>
        <a href="/" class="btn">{{ __('errors.to_main') }}</a>
    </div>
</body>
</html>
