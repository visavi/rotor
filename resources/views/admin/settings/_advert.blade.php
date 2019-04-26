@section('header')
    <h1>Реклама на сайте</h1>
@stop

<form action="/admin/settings?act=advert" method="post">
    @csrf
    <div class="form-group{{ hasError('sets[rekusershow]') }}">
        <label for="rekusershow">Кол. рекламных ссылок:</label>
        <input type="number" class="form-control" id="rekusershow" name="sets[rekusershow]" maxlength="2" value="{{ getInput('sets.rekusershow', $settings['rekusershow']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[rekusershow]') }}</div>
    </div>

    <div class="form-group{{ hasError('sets[rekuserprice]') }}">
        <label for="rekuserprice">Цена рекламы:</label>
        <input type="number" class="form-control" id="rekuserprice" name="sets[rekuserprice]" maxlength="8" value="{{ getInput('sets.rekuserprice', $settings['rekuserprice']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[rekuserprice]') }}</div>
    </div>

    <div class="form-group{{ hasError('sets[rekuserpoint]') }}">
        <label for="rekuserpoint">Актива для размещения рекламы:</label>
        <input type="number" class="form-control" id="rekuserpoint" name="sets[rekuserpoint]" maxlength="3" value="{{ getInput('sets.rekuserpoint', $settings['rekuserpoint']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[rekuserpoint]') }}</div>
    </div>

    <div class="form-group{{ hasError('sets[rekuseroptprice]') }}">
        <label for="rekuseroptprice">Цена опций (жирный текст, цвет):</label>
        <input type="number" class="form-control" id="rekuseroptprice" name="sets[rekuseroptprice]" maxlength="8" value="{{ getInput('sets.rekuseroptprice.', $settings['rekuseroptprice']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[rekuseroptprice]') }}</div>
    </div>

    <div class="form-group{{ hasError('sets[rekusertime]') }}">
        <label for="rekusertime">Срок рекламы (часов):</label>
        <input type="number" class="form-control" id="rekusertime" name="sets[rekusertime]" maxlength="3" value="{{ getInput('sets.rekusertime', $settings['rekusertime']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[rekusertime]') }}</div>
    </div>

    <div class="form-group{{ hasError('sets[rekusertotal]') }}">
        <label for="rekusertotal">Максимум ссылок разрешено:</label>
        <input type="number" class="form-control" id="rekusertotal" name="sets[rekusertotal]" maxlength="2" value="{{ getInput('sets.rekusertotal', $settings['rekusertotal']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[rekusertotal]') }}</div>
    </div>

    <div class="form-group{{ hasError('sets[rekuserpost]') }}">
        <label for="rekuserpost">Листинг всех ссылок:</label>
        <input type="number" class="form-control" id="rekuserpost" name="sets[rekuserpost]" maxlength="2" value="{{ getInput('sets.rekuserpost', $settings['rekuserpost']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[rekuserpost]') }}</div>
    </div>

    <button class="btn btn-primary">Сохранить</button>
</form>
