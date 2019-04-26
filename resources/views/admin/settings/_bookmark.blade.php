@section('header')
    <h1>Закладки / Голосования / Приват</h1>
@stop

<form action="/admin/settings?act=bookmark" method="post">
    @csrf
    <div class="form-group{{ hasError('sets[privatpost]') }}">
        <label for="privatpost">Писем в привате на стр.:</label>
        <input type="number" class="form-control" id="privatpost" name="sets[privatpost]" maxlength="2" value="{{ getInput('sets.privatpost', $settings['privatpost']) }}" required>
        {!! textError('sets[privatpost]') !!}
    </div>

    <div class="form-group{{ hasError('sets[privatprotect]') }}">
        <label for="privatprotect">Порог выключения защитной картинки:</label>
        <input type="number" class="form-control" id="privatprotect" name="sets[privatprotect]" maxlength="4" value="{{ getInput('sets.privatprotect', $settings['privatprotect']) }}" required>
        {!! textError('sets[privatprotect]') !!}
    </div>

    <div class="form-group{{ hasError('sets[contactlist]') }}">
        <label for="contactlist">Листинг в контакт-листе:</label>
        <input type="number" class="form-control" id="contactlist" name="sets[contactlist]" maxlength="2" value="{{ getInput('sets.contactlist', $settings['contactlist']) }}" required>
        {!! textError('sets[contactlist]') !!}
    </div>

    <div class="form-group{{ hasError('sets[ignorlist]') }}">
        <label for="ignorlist">Листинг в игнор-листе:</label>
        <input type="number" class="form-control" id="ignorlist" name="sets[ignorlist]" maxlength="2" value="{{ getInput('sets.ignorlist', $settings['ignorlist']) }}" required>
        {!! textError('sets[ignorlist]') !!}
    </div>

    <div class="form-group{{ hasError('sets[limitcontact]') }}">
        <label for="limitcontact">Максимальное кол. в контакт-листе:</label>
        <input type="number" class="form-control" id="limitcontact" name="sets[limitcontact]" maxlength="2" value="{{ getInput('sets.limitcontact', $settings['limitcontact']) }}" required>
        {!! textError('sets[limitcontact]') !!}
    </div>

    <div class="form-group{{ hasError('sets[limitignore]') }}">
        <label for="limitignore">Максимальное кол. в игнор-листе:</label>
        <input type="number" class="form-control" id="limitignore" name="sets[limitignore]" maxlength="2" value="{{ getInput('sets.limitignore', $settings['limitignore']) }}" required>
        {!! textError('sets[limitignore]') !!}
    </div>

    <div class="form-group{{ hasError('sets[allvotes]') }}">
        <label for="allvotes">Кол-во голосований на стр.:</label>
        <input type="number" class="form-control" id="allvotes" name="sets[allvotes]" maxlength="2" value="{{ getInput('sets.allvotes', $settings['allvotes']) }}" required>
        {!! textError('sets[allvotes]') !!}
    </div>

    <button class="btn btn-primary">Сохранить</button>
</form>
