<form method="post">
    @csrf
    <div class="form-group{{ hasError('place') }}">
        <label for="place">{{ __('admin.paid_adverts.place') }}:</label>

        <?php $inputStatus = getInput('place', $advert->place); ?>
        <select class="form-control" name="place" id="place">
            @foreach ($places as $place)
                <?php $selected = ($place === $inputStatus) ? ' selected' : ''; ?>
                <option value="{{ $place }}"{{ $selected }}>{{ __('admin.paid_adverts.' . $place) }}</option>
            @endforeach
        </select>

        <div class="invalid-feedback">{{ textError('status') }}</div>
    </div>


    <div class="form-group{{ hasError('site') }}">
        <label for="site">{{ __('admin.paid_adverts.link') }}:</label>
        <input name="site" class="form-control" id="site" maxlength="100" placeholder="{{ __('admin.paid_adverts.link') }}" value="{{ getInput('site', $advert->site) }}" required>
        <div class="invalid-feedback">{{ textError('site') }}</div>
    </div>

    <div class="form-group{{ hasError('names') }}">
        <div class="js-advert-list">
            <?php $names = array_values(array_diff((array) getInput('names', $advert->names), [''])) ?>

            @for ($i = 0; $i < max(1, count($names)); $i++)
                @if ($i === 0)
                    <label for="names{{ $i }}">{{ __('admin.paid_adverts.names') }}:</label>
                    <a class="js-advert-add" href="#" data-toggle="tooltip" title="{{ __('main.add') }}"><i class="fas fa-plus-square"></i></a>
                    <input type="text" name="names[]" class="form-control" id="names{{ $i }}" value="{{ $names[$i] ?? '' }}" maxlength="35" placeholder="{{ __('admin.paid_adverts.name') }}">
                @else
                    <div class="input-group mt-1 js-advert-append">
                        <input class="form-control" name="names[]" type="text" value="{{ $names[$i] ?? '' }}" maxlength="35" placeholder="{{ __('admin.paid_adverts.name') }}">
                        <span class="input-group-append">
                            <span class="input-group-text">
                                <a class="js-advert-remove" href="#"><i class="fa fa-times"></i></a>
                            </span>
                        </span>
                    </div>
                @endif
            @endfor
        </div>
        <div class="invalid-feedback">{{ textError('names') }}</div>
    </div>

    <div class="form-group{{ hasError('color') }}">
        <label for="color">{{ __('admin.paid_adverts.color') }}:</label>

        <div class="input-group colorpick">
            <input class="form-control col-sm-4 js-color" id="color" name="color" type="text" maxlength="7" placeholder="{{ __('admin.paid_adverts.color') }}" value="{{ getInput('color', $advert->color) }}">
            <span class="input-group-append">
                <span class="input-group-text colorpicker-input-addon"><i></i></span>
            </span>
        </div>

        <div class="invalid-feedback">{{ textError('color') }}</div>
    </div>

    <div class="custom-control custom-checkbox">
        <input type="hidden" value="0" name="bold">
        <input type="checkbox" class="custom-control-input js-bold" value="1" name="bold" id="bold"{{ getInput('bold', $advert->bold) ? ' checked' : '' }}>
        <label class="custom-control-label" for="bold">{{ __('admin.paid_adverts.bold') }}</label>
    </div>

    <div class="form-group{{ hasError('term') }}">
        <label for="term">{{ __('admin.paid_adverts.term') }}:</label>
        <input class="form-control" type="datetime-local" name="term" id="term" value="{{ getInput('term', dateFixed($advert->deleted_at, 'Y-m-d\TH:i')) }}" required>
        <div class="invalid-feedback">{{ textError('term') }}</div>
    </div>

    <div class="form-group{{ hasError('comment') }}">
        <label for="message">{{ __('main.comment') }}:</label>
        <textarea class="form-control markItUp" id="comment" rows="5" name="comment">{{ getInput('comment', $advert->comment) }}</textarea>
        <div class="invalid-feedback">{{ textError('comment') }}</div>
    </div>

    <button class="btn btn-primary">{{ __('main.save') }}</button>
</form>

@push('scripts')
    <script>
        $(".js-advert-add").click(function () {
            $('.js-advert-list').append('<div class="input-group mt-1 js-advert-append">' +
                '<input class="form-control" id="name" name="names[]" type="text" value="" maxlength="35" placeholder="<?= __('admin.paid_adverts.name') ?>">' +
                    '<span class="input-group-append">' +
                        '<span class="input-group-text">' +
                            '<a class="js-advert-remove" href="#"><i class="fa fa-times"></i></a>' +
                        '</span>' +
                    '</span>' +
            '</div>');

            return false;
        });

        $(document).on('click', '.js-advert-remove', function () {
            $(this).closest('.js-advert-append').remove();

            return false;
        });
    </script>
@endpush