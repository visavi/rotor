<h3>Реклама на сайте</h3>

<form action="/admin/settings?act=advert" method="post">
    <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

    <div class="form-group{{ hasError('sets[rekusershow]') }}">
        <label for="rekusershow">Кол. рекламных ссылок:</label>
        <input type="number" class="form-control" id="rekusershow" name="sets[rekusershow]" maxlength="2" value="{{ getInput('sets.rekusershow', $settings['rekusershow']) }}" required>
        {!! textError('sets[rekusershow]') !!}
    </div>

    <div class="form-group{{ hasError('sets[rekuserprice]') }}">
        <label for="rekuserprice">Цена рекламы:</label>
        <input type="number" class="form-control" id="rekuserprice" name="sets[rekuserprice]" maxlength="8" value="{{ getInput('sets.rekuserprice', $settings['rekuserprice']) }}" required>
        {!! textError('sets[rekuserprice]') !!}
    </div>

    <div class="form-group{{ hasError('sets[rekuseroptprice]') }}">
        <label for="rekuseroptprice">Цена опций (жирный текст, цвет):</label>
        <input type="number" class="form-control" id="rekuseroptprice" name="sets[rekuseroptprice]" maxlength="8" value="{{ getInput('sets.rekuseroptprice.', $settings['rekuseroptprice']) }}" required>
        {!! textError('sets[rekuseroptprice]') !!}
    </div>

    <div class="form-group{{ hasError('sets[rekusertime]') }}">
        <label for="rekusertime">Срок рекламы (часов):</label>
        <input type="number" class="form-control" id="rekusertime" name="sets[rekusertime]" maxlength="3" value="{{ getInput('sets.rekusertime', $settings['rekusertime']) }}" required>
        {!! textError('sets[rekusertime]') !!}
    </div>

    <div class="form-group{{ hasError('sets[rekusertotal]') }}">
        <label for="rekusertotal">Максимум ссылок разрешено:</label>
        <input type="number" class="form-control" id="rekusertotal" name="sets[rekusertotal]" maxlength="2" value="{{ getInput('sets.rekusertotal', $settings['rekusertotal']) }}" required>
        {!! textError('sets[rekusertotal]') !!}
    </div>

    <div class="form-group{{ hasError('sets[rekuserpost]') }}">
        <label for="rekuserpost">Листинг всех ссылок:</label>
        <input type="number" class="form-control" id="rekuserpost" name="sets[rekuserpost]" maxlength="2" value="{{ getInput('sets.rekuserpost', $settings['rekuserpost']) }}" required>
        {!! textError('sets[rekuserpost]') !!}
    </div>

    <button class="btn btn-primary">Сохранить</button>
</form>
