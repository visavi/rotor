@section('header')
    <h1>Гостевая / Новости</h1>
@stop

<form action="/admin/settings?act=guest" method="post">
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

    <div class="custom-control custom-checkbox">
        <input type="hidden" value="0" name="sets[bookadds]">
        <input type="checkbox" class="custom-control-input" value="1" name="sets[bookadds]" id="bookadds"{{ getInput('sets.bookadds', $settings['bookadds']) ? ' checked' : '' }}>
        <label class="custom-control-label" for="bookadds">Разрешить гостям писать в гостевой</label>
    </div>

    <div class="custom-control custom-checkbox">
        <input type="hidden" value="0" name="sets[bookscores]">
        <input type="checkbox" class="custom-control-input" value="1" name="sets[bookscores]" id="bookscores"{{ getInput('sets.bookscores', $settings['bookscores']) ? ' checked' : '' }}>
        <label class="custom-control-label" for="bookscores">Начислять баллы в гостевой</label>
    </div>

    <div class="form-group{{ hasError('sets[bookpost]') }}">
        <label for="bookpost">Сообщений в гостевой на стр:</label>
        <input type="number" class="form-control" id="postnews" name="sets[bookpost]" maxlength="2" value="{{ getInput('sets.bookpost', $settings['bookpost']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[bookpost]') }}</div>
    </div>

    <div class="form-group{{ hasError('sets[guestsuser]') }}">
        <label for="guestsuser">Неавторизованный пользователь:</label>
        <input type="text" class="form-control" id="guestsuser" name="sets[guestsuser]" maxlength="20" value="{{ getInput('sets.guestsuser', $settings['guestsuser']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[guestsuser]') }}</div>
    </div>

    <div class="form-group{{ hasError('sets[guesttextlength]') }}">
        <label for="guesttextlength">Символов в сообщении гостевой:</label>
        <input type="number" class="form-control" id="guesttextlength" name="sets[guesttextlength]" maxlength="5" value="{{ getInput('sets.guesttextlength', $settings['guesttextlength']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[guesttextlength]') }}</div>
    </div>

    <div class="form-group{{ hasError('sets[comment_length]') }}">
        <label for="comment_length">Символов в комментариях:</label>
        <input type="number" class="form-control" id="comment_length" name="sets[comment_length]" maxlength="5" value="{{ getInput('sets.comment_length', $settings['comment_length']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[comment_length]') }}</div>
    </div>

    <div class="form-group{{ hasError('sets[guesttextlength]') }}">
        <label for="chatpost">Сообщений в админ-чате на стр:</label>
        <input type="number" class="form-control" id="chatpost" name="sets[chatpost]" maxlength="2" value="{{ getInput('sets.chatpost', $settings['chatpost']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[chatpost]') }}</div>
    </div>

    <button class="btn btn-primary">Сохранить</button>
</form>
