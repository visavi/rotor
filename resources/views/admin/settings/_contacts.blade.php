@section('header')
    <h1>{{ __('settings.contacts') }}</h1>
@stop

<form action="/admin/settings?act=contacts" method="post">
    @csrf
    <div class="form-group{{ hasError('sets[contactlist]') }}">
        <label for="contactlist">{{ __('settings.contacts_per_page') }}:</label>
        <input type="number" class="form-control" id="contactlist" name="sets[contactlist]" maxlength="2" value="{{ getInput('sets.contactlist', $settings['contactlist']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[contactlist]') }}</div>
    </div>

    <div class="form-group{{ hasError('sets[ignorlist]') }}">
        <label for="ignorlist">{{ __('settings.ignores_per_page') }}:</label>
        <input type="number" class="form-control" id="ignorlist" name="sets[ignorlist]" maxlength="2" value="{{ getInput('sets.ignorlist', $settings['ignorlist']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[ignorlist]') }}</div>
    </div>

    <div class="form-group{{ hasError('sets[limitcontact]') }}">
        <label for="limitcontact">{{ __('settings.contacts_max_users') }}:</label>
        <input type="number" class="form-control" id="limitcontact" name="sets[limitcontact]" maxlength="4" value="{{ getInput('sets.limitcontact', $settings['limitcontact']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[limitcontact]') }}</div>
    </div>

    <div class="form-group{{ hasError('sets[limitignore]') }}">
        <label for="limitignore">{{ __('settings.ignores_max_users') }}:</label>
        <input type="number" class="form-control" id="limitignore" name="sets[limitignore]" maxlength="4" value="{{ getInput('sets.limitignore', $settings['limitignore']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[limitignore]') }}</div>
    </div>

    <button class="btn btn-primary">{{ __('main.save') }}</button>
</form>
