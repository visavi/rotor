@section('header')
    <h1>{{ __('settings.messages') }}</h1>
@stop

<form method="post">
    @csrf
    <div class="mb-3{{ hasError('sets[privatpost]') }}">
        <label for="privatpost" class="form-label">{{ __('settings.messages_per_page') }}:</label>
        <input type="number" class="form-control" id="privatpost" name="sets[privatpost]" maxlength="2" value="{{ getInput('sets.privatpost', $settings['privatpost']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[privatpost]') }}</div>
    </div>

    <div class="mb-3{{ hasError('sets[privatprotect]') }}">
        <label for="privatprotect" class="form-label">{{ __('settings.messages_captcha') }}:</label>
        <input type="number" class="form-control" id="privatprotect" name="sets[privatprotect]" maxlength="4" value="{{ getInput('sets.privatprotect', $settings['privatprotect']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[privatprotect]') }}</div>
    </div>

    <button class="btn btn-primary">{{ __('main.save') }}</button>
</form>
