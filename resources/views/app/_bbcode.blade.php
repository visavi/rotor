<!DOCTYPE html>
<html lang="{{ setting('language') }}">
<head>
    <meta charset="utf-8">
    <title>Rotor</title>
    @include('app/_styles')
</head>
<body>
    {!! bbCode($message) !!}
    @include('app/_scripts')
</body>
</html>
