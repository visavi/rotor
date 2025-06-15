@section('header')
    <h1>{{ __('settings.guestbook') }}</h1>
@stop

<form method="post">
    @csrf
    <div class="form-check mb-3">
        <input type="hidden" value="0" name="sets[bookadds]">
        <input type="checkbox" class="form-check-input" value="1" name="sets[bookadds]" id="bookadds"{{ getInput('sets.bookadds', $settings['bookadds']) ? ' checked' : '' }}>
        <label class="form-check-label" for="bookadds">{{ __('settings.guestbook_guests_allow') }}</label>
    </div>

    <div class="form-check mb-3">
        <input type="hidden" value="0" name="sets[bookscores]">
        <input type="checkbox" class="form-check-input" value="1" name="sets[bookscores]" id="bookscores"{{ getInput('sets.bookscores', $settings['bookscores']) ? ' checked' : '' }}>
        <label class="form-check-label" for="bookscores">{{ __('settings.guestbook_points_add') }}</label>
    </div>

    <div class="form-check mb-3">
        <input type="hidden" value="0" name="sets[guest_moderation]">
        <input type="checkbox" class="form-check-input" value="1" name="sets[guest_moderation]" id="guest_moderation"{{ getInput('sets.guest_moderation', $settings['guest_moderation']) ? ' checked' : '' }}>
        <label class="form-check-label" for="guest_moderation">{{ __('settings.guestbook_moderation') }}</label>
    </div>

    <div class="mb-3{{ hasError('sets[bookpost]') }}">
        <label for="bookpost" class="form-label">{{ __('settings.guestbook_per_page') }}:</label>
        <input type="number" class="form-control" id="bookpost" name="sets[bookpost]" maxlength="2" value="{{ getInput('sets.bookpost', $settings['bookpost']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[bookpost]') }}</div>
    </div>

    <div class="mb-3">
        <label for="guestbook_text_min" class="form-label">{{ __('settings.guestbook_text_length') }}:</label>
        <div class="d-flex gap-2">
            <input type="number" class="form-control{{ hasError('sets[guestbook_text_min]') }}" id="guestbook_text_min" name="sets[guestbook_text_min]" value="{{ old('sets.guestbook_text_min', $settings['guestbook_text_min']) }}" placeholder="{{ __('main.min') }}" required>
            <input type="number" class="form-control{{ hasError('sets[guestbook_text_max]') }}" name="sets[guestbook_text_max]" value="{{ old('sets.guestbook_text_max', $settings['guestbook_text_max']) }}" placeholder="{{ __('main.max') }}" required>
        </div>
        <div class="invalid-feedback d-block">
            <div>{{ textError('sets[guestbook_title_max]') }}</div>
            <div>{{ textError('sets[guestbook_text_max]') }}</div>
        </div>
    </div>

    <button class="btn btn-primary">{{ __('main.save') }}</button>
</form>
