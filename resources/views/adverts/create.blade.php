@extends('layout')

@section('title')
    {{ __('adverts.create_advert') }}
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/menu">{{ __('main.menu') }}</a></li>
            <li class="breadcrumb-item"><a href="/adverts">{{ __('index.advertising') }}</a></li>
            <li class="breadcrumb-item active">{{ __('adverts.create_advert') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    {{ __('main.cash_money') }}: <b>{{ plural(getUser('money'), setting('moneyname')) }}</b><br><br>

    <div class="form">
        <form method="post" action="/adverts/create">
            @csrf
            <div class="form-group{{ hasError('site') }}">
                <label for="site">{{ __('adverts.link') }}:</label>
                <input name="site" class="form-control" id="site" maxlength="100" placeholder="{{ __('adverts.link') }}" value="{{ getInput('site') }}" required>
                <div class="invalid-feedback">{{ textError('site') }}</div>
            </div>

            <div class="form-group{{ hasError('name') }}">
                <label for="name">{{ __('adverts.name') }}:</label>
                <input name="name" class="form-control" id="name" maxlength="35" placeholder="{{ __('adverts.name') }}" value="{{ getInput('name') }}" required>
                <div class="invalid-feedback">{{ textError('name') }}</div>
            </div>

            <div class="form-group{{ hasError('color') }}">
                <label for="color">{{ __('adverts.color') }}:</label>

                <div class="input-group colorpick">
                    <input class="form-control col-sm-4 js-color" id="color" name="color" type="text" maxlength="7" placeholder="{{ __('adverts.color') }}" value="{{ getInput('color') }}">
                    <span class="input-group-append">
                        <span class="input-group-text colorpicker-input-addon"><i></i></span>
                    </span>
                </div>

                <div class="invalid-feedback">{{ textError('color') }}</div>
            </div>

            <div class="custom-control custom-checkbox">
                <input type="hidden" value="0" name="bold">
                <input type="checkbox" class="custom-control-input js-bold" value="1" name="bold" id="bold"{{ getInput('bold') ? ' checked' : '' }}>
                <label class="custom-control-label" for="bold">{{ __('adverts.bold') }}</label>
            </div>

            {!! view('app/_captcha') !!}

            <button class="btn btn-primary">{{ __('adverts.buy_for') }} <span class="js-price">{{ setting('rekuserprice') }}</span></button>
        </form>
    </div><br>

    {!! __('adverts.rules_text', ['price' =>  plural(setting('rekuserprice'), setting('moneyname')), 'time' => setting('rekusertime'), 'optprice' => plural(setting('rekuseroptprice'), setting('moneyname'))]) !!}<br>
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
