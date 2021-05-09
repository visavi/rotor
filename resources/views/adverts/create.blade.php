@extends('layout')

@section('title', __('adverts.create_advert'))

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

    <div class="section-form mb-3 shadow">
        <form method="post" action="/adverts/create">
            @csrf
            <div class="mb-3{{ hasError('site') }}">
                <label for="site" class="form-label">{{ __('adverts.link') }}:</label>
                <input name="site" class="form-control" id="site" maxlength="100" placeholder="{{ __('adverts.link') }}" value="{{ getInput('site') }}" required>
                <div class="invalid-feedback">{{ textError('site') }}</div>
            </div>

            <div class="mb-3{{ hasError('name') }}">
                <label for="name" class="form-label">{{ __('adverts.name') }}:</label>
                <input name="name" class="form-control" id="name" maxlength="35" placeholder="{{ __('adverts.name') }}" value="{{ getInput('name') }}" required>
                <div class="invalid-feedback">{{ textError('name') }}</div>
            </div>

            <?php $color = getInput('color'); ?>
            <div class="col-sm-4 mb-3{{ hasError('color') }}">
                <label for="color" class="form-label">{{ __('adverts.color') }}:</label>
                <div class="input-group">
                    <input type="text" name="color" class="form-control colorpicker js-color" id="color" maxlength="7" value="{{ $color }}" placeholder="{{ __('adverts.color') }}">
                    <input type="color" class="form-control form-control-color colorpicker-addon js-color" value="{{ $color }}">
                </div>
                <div class="invalid-feedback">{{ textError('color') }}</div>
            </div>

            <div class="form-check">
                <input type="hidden" value="0" name="bold">
                <input type="checkbox" class="form-check-input js-bold" value="1" name="bold" id="bold"{{ getInput('bold') ? ' checked' : '' }}>
                <label class="form-check-label" for="bold">{{ __('adverts.bold') }}</label>
            </div>

            {{ getCaptcha() }}

            <button class="btn btn-primary">{{ __('adverts.buy_for') }} <span class="js-price">{{ setting('rekuserprice') }}</span></button>
        </form>
    </div>

    <div class="text-muted fst-italic">
        {!! __('adverts.rules_text', ['price' =>  plural(setting('rekuserprice'), setting('moneyname')), 'time' => setting('rekusertime'), 'optprice' => plural(setting('rekuseroptprice'), setting('moneyname'))]) !!}
    </div>
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
