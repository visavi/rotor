<?php header('Content-type:text/html; charset=utf-8'); ?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <title>
        @section('title')
            {{ setting('title') }}
        @show
    </title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
</head>
<body>
    @yield('content')
</body>
</html>
