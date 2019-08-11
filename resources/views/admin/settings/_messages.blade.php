@section('header')
    <h1>{{ trans('settings.messages') }}</h1>
@stop

<form action="/admin/settings?act=messages" method="post">
    @csrf
    <div class="form-group{{ hasError('sets[privatpost]') }}">
        <label for="privatpost">{{ trans('settings.messages_per_page') }}:</label>
        <input type="number" class="form-control" id="privatpost" name="sets[privatpost]" maxlength="2" value="{{ getInput('sets.privatpost', $settings['privatpost']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[privatpost]') }}</div>
    </div>

    <div class="form-group{{ hasError('sets[privatprotect]') }}">
        <label for="privatprotect">{{ trans('settings.messages_captcha') }}:</label>
        <input type="number" class="form-control" id="privatprotect" name="sets[privatprotect]" maxlength="4" value="{{ getInput('sets.privatprotect', $settings['privatprotect']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[privatprotect]') }}</div>
    </div>

    <button class="btn btn-primary">{{ trans('main.save') }}</button>
</form>
