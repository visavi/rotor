@section('header')
    <h1>{{ trans('settings.news') }}</h1>
@stop

<form action="/admin/settings?act=news" method="post">
    @csrf
    <div class="form-group{{ hasError('sets[sendprivatmailday]') }}">
        <label for="lastnews">Кол. новостей на главной:</label>
        <input type="number" class="form-control" id="lastnews" name="sets[lastnews]" maxlength="2" value="{{ getInput('sets.lastnews', $settings['lastnews']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[lastnews]') }}</div>
    </div>

    <div class="form-group{{ hasError('sets[postnews]') }}">
        <label for="postnews">Новостей на страницу:</label>
        <input type="number" class="form-control" id="postnews" name="sets[postnews]" maxlength="2" value="{{ getInput('sets.postnews', $settings['postnews']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[postnews]') }}</div>
    </div>

    <button class="btn btn-primary">{{ trans('main.save') }}</button>
</form>
