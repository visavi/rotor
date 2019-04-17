@extends('layout')

@section('title')
    {{ trans('adverts.create_advert') }}
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/menu">{{ trans('main.menu') }}</a></li>
            <li class="breadcrumb-item"><a href="/adverts">{{ trans('adverts.title') }}</a></li>
            <li class="breadcrumb-item active">{{ trans('adverts.create_advert') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    {{ trans('main.cash_money') }}: <b>{{ plural(getUser('money'), setting('moneyname')) }}</b><br><br>

    <div class="form">
        <form method="post" action="/adverts/create">
            <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

            <div class="form-group{{ hasError('site') }}">
                <label for="site">{{ trans('adverts.link') }}:</label>
                <input name="site" class="form-control" id="site" maxlength="100" placeholder="{{ trans('adverts.link') }}" value="{{ getInput('site') }}" required>
                {!! textError('site') !!}
            </div>

            <div class="form-group{{ hasError('name') }}">
                <label for="name">{{ trans('adverts.name') }}:</label>
                <input name="name" class="form-control" id="name" maxlength="35" placeholder="{{ trans('adverts.name') }}" value="{{ getInput('name') }}" required>
                {!! textError('name') !!}
            </div>

            <div class="form-group{{ hasError('color') }}">
                <label for="color">{{ trans('adverts.color') }}:</label>

                <div class="input-group colorpick">
                    <input class="form-control col-sm-4 js-color" id="color" name="color" type="text" maxlength="7" placeholder="{{ trans('adverts.color') }}" value="{{ getInput('color') }}">
                    <div class="input-group-append">
                        <span class="input-group-text input-group-addon"><i></i></span>
                    </div>
                </div>

                {!! textError('color') !!}
            </div>

            <div class="custom-control custom-checkbox">
                <input type="hidden" value="0" name="bold">
                <input type="checkbox" class="custom-control-input js-bold" value="1" name="bold" id="bold"{{ getInput('bold') ? ' checked' : '' }}>
                <label class="custom-control-label" for="bold">{{ trans('adverts.bold') }}</label>
            </div>

            {!! view('app/_captcha') !!}

            <button class="btn btn-primary">{{ trans('adverts.buy_for') }} <span class="js-price">{{ setting('rekuserprice') }}</span></button>
        </form>
    </div><br>

    {!! trans('adverts.rules_text') !!}<br>
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
