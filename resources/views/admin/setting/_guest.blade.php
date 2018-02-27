<h3>Гостевая / Новости</h3>

<form action="/admin/setting?act=guest" method="post">
    <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

    <div class="form-group{{ hasError('sets[sendprivatmailday]') }}">
        <label for="lastnews">Кол. новостей на главной:</label>
        <input type="number" class="form-control" id="lastnews" name="sets[lastnews]" maxlength="2" value="{{ getInput('sets.lastnews', $settings['lastnews']) }}" required>
        {!! textError('sets[lastnews]') !!}
    </div>

    <div class="form-group{{ hasError('sets[postnews]') }}">
        <label for="postnews">Новостей на страницу:</label>
        <input type="number" class="form-control" id="postnews" name="sets[postnews]" maxlength="2" value="{{ getInput('sets.postnews', $settings['postnews']) }}" required>
        {!! textError('sets[postnews]') !!}
    </div>

    <div class="form-check">
        <label class="form-check-label">
            <input type="hidden" value="0" name="sets[bookadds]">
            <input name="sets[bookadds]" class="form-check-input" type="checkbox" value="1"{{ getInput('sets.bookadds', $settings['bookadds']) ? ' checked' : '' }}>
            Разрешить гостям писать в гостевой
        </label>
    </div>

    <div class="form-check">
        <label class="form-check-label">
            <input type="hidden" value="0" name="sets[bookscores]">
            <input name="sets[bookscores]" class="form-check-input" type="checkbox" value="1"{{ getInput('sets.bookscores', $settings['bookscores']) ? ' checked' : '' }}>
            Начислять баллы в гостевой
        </label>
    </div>

    <div class="form-group{{ hasError('sets[bookpost]') }}">
        <label for="bookpost">Сообщений в гостевой на стр:</label>
        <input type="number" class="form-control" id="postnews" name="sets[bookpost]" maxlength="2" value="{{ getInput('sets.bookpost', $settings['bookpost']) }}" required>
        {!! textError('sets[bookpost]') !!}
    </div>

    <div class="form-group{{ hasError('sets[guestsuser]') }}">
        <label for="guestsuser">Неавторизованный пользователь:</label>
        <input type="text" class="form-control" id="guestsuser" name="sets[guestsuser]" maxlength="20" value="{{ getInput('sets.guestsuser', $settings['guestsuser']) }}" required>
        {!! textError('sets[guestsuser]') !!}
    </div>

    <div class="form-group{{ hasError('sets[guesttextlength]') }}">
        <label for="guesttextlength">Символов в сообщении гостевой:</label>
        <input type="number" class="form-control" id="guesttextlength" name="sets[guesttextlength]" maxlength="5" value="{{ getInput('sets.guesttextlength', $settings['guesttextlength']) }}" required>
        {!! textError('sets[guesttextlength]') !!}
    </div>

    <div class="form-group{{ hasError('sets[guesttextlength]') }}">
        <label for="chatpost">Сообщений в админ-чате на стр:</label>
        <input type="number" class="form-control" id="chatpost" name="sets[chatpost]" maxlength="2" value="{{ getInput('sets.chatpost', $settings['chatpost']) }}" required>
        {!! textError('sets[chatpost]') !!}
    </div>

    <button class="btn btn-primary">Сохранить</button>
</form>
