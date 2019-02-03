@extends('layout')

@section('title')
    Размещение рекламы
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/menu">Мое меню</a></li>
            <li class="breadcrumb-item"><a href="/reklama">Реклама на сайте</a></li>
            <li class="breadcrumb-item active">Размещение рекламы</li>
        </ol>
    </nav>
@stop

@section('content')
    У вас в наличии: <b>{{ plural(getUser('money'), setting('moneyname')) }}</b><br><br>

    <div class="form">
        <form method="post" action="/reklama/create">
            <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

            <div class="form-group{{ hasError('site') }}">
                <label for="site">Адрес сайта:</label>
                <input name="site" class="form-control" id="site" maxlength="100" placeholder="Адрес сайта" value="{{ getInput('site') }}" required>
                {!! textError('site') !!}
            </div>

            <div class="form-group{{ hasError('name') }}">
                <label for="name">Название:</label>
                <input name="name" class="form-control" id="name" maxlength="35" placeholder="Название" value="{{ getInput('name') }}" required>
                {!! textError('name') !!}
            </div>

            <div class="form-group{{ hasError('color') }}">
                <label for="color">Код цвета:</label>

                <div class="input-group colorpick">
                    <input class="form-control col-sm-4 js-color" id="color" name="color" type="text" maxlength="7" placeholder="Код цвета" value="{{ getInput('color') }}">
                    <div class="input-group-append">
                        <span class="input-group-text input-group-addon"><i></i></span>
                    </div>
                </div>

                {!! textError('color') !!}
            </div>

            <div class="custom-control custom-checkbox">
                <input type="hidden" value="0" name="bold">
                <input type="checkbox" class="custom-control-input js-bold" value="1" name="bold" id="bold"{{ getInput('bold') ? ' checked' : '' }}>
                <label class="custom-control-label" for="bold">Жирный текст</label>
            </div>

            {!! view('app/_captcha') !!}

            <button class="btn btn-primary">Купить за <span class="js-price">{{ setting('rekuserprice') }}</span></button>
        </form>
    </div><br>

    Стоимость размещения ссылки {{ plural(setting('rekuserprice'), setting('moneyname')) }} за {{ setting('rekusertime') }} часов<br>
    Цвет и жирный текст опционально, стоимость каждой опции {{ plural(setting('rekuseroptprice'), setting('moneyname')) }}<br>
    Ссылка прокручивается на всех страницах сайта с другими ссылками пользователей<br>
    В названии ссылки запрещено использовать любые ненормативные и матные слова<br>
    Адрес ссылки не должен направлять на прямое скачивание какого-либо контента<br>
    Запрещены ссылки на сайты с алярмами и порно<br>
    За нарушение правил предусмотрено наказание в виде бана<br><br>
@stop

@push('scripts')
    <script>
        $(document).ready(function() {
            var rekuserprice    = <?= setting('rekuserprice'); ?>;
            var rekuseroptprice = <?= setting('rekuseroptprice'); ?>;
            var price           = $('.js-price');
            var bold            = $('.js-bold');
            var color           = $('.js-color');
            var recount = function() {
                var newprice = parseInt(rekuserprice);

                if (bold.is(':checked')) {
                    newprice += parseInt(rekuseroptprice);
                }
                if (color.val().length) {
                    newprice += parseInt(rekuseroptprice);
                }
                price.html(newprice);
            };
            recount();
            bold.on('change', recount);
            color.on('input change', recount);
        });
    </script>
@endpush
