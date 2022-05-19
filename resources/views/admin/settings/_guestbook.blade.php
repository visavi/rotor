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

    <div class="mb-3{{ hasError('sets[guesttextlength]') }}">
        <label for="guesttextlength" class="form-label">{{ __('settings.guestbook_symbols') }}:</label>
        <input type="number" class="form-control" id="guesttextlength" name="sets[guesttextlength]" maxlength="5" value="{{ getInput('sets.guesttextlength', $settings['guesttextlength']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[guesttextlength]') }}</div>
    </div>

    <button class="btn btn-primary">{{ __('main.save') }}</button>
</form>
