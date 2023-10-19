@section('header')
    <h1>{{ __('settings.invitations') }}</h1>
@stop

<form method="post">
    @csrf
    <div class="mb-3{{ hasError('sets[invite_days]') }}">
        <label for="invite_days" class="form-label">{{ __('settings.invite_days') }}:</label>
        <input type="number" class="form-control" id="invite_days" name="sets[invite_days]" maxlength="3" value="{{ old('sets.invite_days', $settings['invite_days']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[invite_days]') }}</div>
    </div>

    <div class="mb-3{{ hasError('sets[invite_rating]') }}">
        <label for="invite_rating" class="form-label">{{ __('settings.invite_rating') }}:</label>
        <input type="number" class="form-control" id="invite_rating" name="sets[invite_rating]" maxlength="3" value="{{ old('sets.invite_rating', $settings['invite_rating']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[invite_rating]') }}</div>
    </div>

    <div class="mb-3{{ hasError('sets[invite_count]') }}">
        <label for="invite_count" class="form-label">{{ __('settings.invite_count') }}:</label>
        <input type="number" class="form-control" id="invite_count" name="sets[invite_count]" maxlength="3" value="{{ old('sets.invite_count', $settings['invite_count']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[invite_count]') }}</div>
    </div>

    <button class="btn btn-primary">{{ __('main.save') }}</button>
</form>
