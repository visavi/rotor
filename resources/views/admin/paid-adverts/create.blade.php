@extends('layout')

@section('title', 'Размещение рекламы')

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ __('index.panel') }}</a></li>
            <li class="breadcrumb-item active"><a href="/admin/paid-adverts">Платная реклама</a></li>
            <li class="breadcrumb-item active">Размещение рекламы</li>
        </ol>
    </nav>
@stop

@section('content')
    <div class="section-form mb-3 shadow">
        <form method="post" action="/admin/paid-adverts/create">
            @csrf
            <div class="form-group{{ hasError('place') }}">
                <label for="place">Place:</label>

                <?php $inputStatus = getInput('place'); ?>
                <select class="form-control" name="place" id="place">
                    @foreach ($places as $place)
                        <?php $selected = ($place === $inputStatus) ? ' selected' : ''; ?>
                        <option value="{{ $place }}"{{ $selected }}>{{ __('admin.paid_adverts.' . $place) }}</option>
                    @endforeach
                </select>

                <div class="invalid-feedback">{{ textError('status') }}</div>
            </div>


            <div class="form-group{{ hasError('site') }}">
                <label for="site">{{ __('adverts.link') }}:</label>
                <input name="site" class="form-control" id="site" maxlength="100" placeholder="{{ __('adverts.link') }}" value="{{ getInput('site') }}" required>
                <div class="invalid-feedback">{{ textError('site') }}</div>
            </div>

            <div class="form-group{{ hasError('names') }} js-advert-names">
                <?php $names = array_values(array_diff((array) getInput('names'), [''])) ?>

                @for ($i = 0; $i < max(1, count($names)); $i++)
                    @if ($i === 0)
                        <label for="names{{ $i }}">{{ __('adverts.name') }}:</label>
                        <a onclick="return addAdvertName()" href="#" data-toggle="tooltip" title="{{ __('main.add') }}"><i class="fas fa-plus-square"></i></a>
                        <input type="text" name="names[]" class="form-control" id="names{{ $i }}" value="{{ $names[$i] ?? '' }}" maxlength="35" placeholder="{{ __('adverts.name') }}">
                    @else
                        <div class="input-group mt-1">
                            <input class="form-control" name="names[]" type="text" value="{{ $names[$i] ?? '' }}" maxlength="35" placeholder="{{ __('adverts.name') }}">
                            <span class="input-group-append">
                                <span class="input-group-text">
                                    <a class="js-advert-remove" href="#"><i class="fa fa-times"></i></a>
                                </span>
                            </span>
                        </div>
                    @endif

                @endfor
                <div class="invalid-feedback">{{ textError('names') }}</div>
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

            <div class="form-group{{ hasError('term') }}">
                <label for="term">term:</label>
                <input class="form-control" type="datetime-local" name="term" id="term" value="{{ getInput('term') }}" required>
                <div class="invalid-feedback">{{ textError('term') }}</div>
            </div>

            <div class="form-group{{ hasError('comment') }}">
                <label for="message">comment:</label>
                <textarea class="form-control markItUp" id="comment" rows="5" name="comment">{{ getInput('comment') }}</textarea>
                <div class="invalid-feedback">{{ textError('comment') }}</div>
            </div>

            <button class="btn btn-primary">Разместить</button>
        </form>
    </div>
@stop

@push('scripts')
    <script>
        function addAdvertName() {
            $('.js-advert-names').append('<div class="input-group mt-1">' +
                '<input class="form-control" id="name" name="names[]" type="text" value="" maxlength="35" placeholder="<?= __('adverts.name') ?>">' +
                    '<span class="input-group-append">' +
                        '<span class="input-group-text">' +
                            '<a class="js-advert-remove" href="#"><i class="fa fa-times"></i></a>' +
                        '</span>' +
                    '</span>' +
            '</div>');

            return false;
        }

        $(document).on('click', '.js-advert-remove', function () {
            $(this).closest('.input-group').remove();

            return false;
        });
    </script>
@endpush
