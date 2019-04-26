@section('header')
    <h1>Постраничная навигация</h1>
@stop

<form action="/admin/settings?act=page" method="post">
    @csrf
    <div class="form-group{{ hasError('sets[userlist]') }}">
        <label for="userlist">Пользователей в юзерлисте:</label>
        <input type="number" class="form-control" id="userlist" name="sets[userlist]" maxlength="2" value="{{ getInput('sets.userlist', $settings['userlist']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[userlist]') }}</div>
    </div>

    <div class="form-group{{ hasError('sets[onlinelist]') }}">
        <label for="onlinelist">Пользователей в онлайне:</label>
        <input type="number" class="form-control" id="onlinelist" name="sets[onlinelist]" maxlength="2" value="{{ getInput('sets.onlinelist', $settings['onlinelist']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[onlinelist]') }}</div>
    </div>

    <div class="form-group{{ hasError('sets[stickerlist]') }}">
        <label for="stickerlist">Стикеров на стр.:</label>
        <input type="number" class="form-control" id="stickerlist" name="sets[stickerlist]" maxlength="2" value="{{ getInput('sets.stickerlist', $settings['stickerlist']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[stickerlist]') }}</div>
    </div>

    <div class="form-group{{ hasError('sets[avtorlist]') }}">
        <label for="avtorlist">Юзеров в рейтинге репутации на стр.:</label>
        <input type="number" class="form-control" id="avtorlist" name="sets[avtorlist]" maxlength="2" value="{{ getInput('sets.avtorlist', $settings['avtorlist']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[avtorlist]') }}</div>
    </div>

    <div class="form-group{{ hasError('sets[banlist]') }}">
        <label for="banlist">Юзеров в списке забаненных:</label>
        <input type="number" class="form-control" id="banlist" name="sets[banlist]" maxlength="2" value="{{ getInput('sets.banlist', $settings['banlist']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[banlist]') }}</div>
    </div>

    <div class="form-group{{ hasError('sets[listbanhist]') }}">
        <label for="listbanhist">Листинг истории банов пользователя:</label>
        <input type="number" class="form-control" id="listbanhist" name="sets[listbanhist]" maxlength="2" value="{{ getInput('sets.listbanhist', $settings['listbanhist']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[listbanhist]') }}</div>
    </div>

    <div class="form-group{{ hasError('sets[usersearch]') }}">
        <label for="usersearch">Юзеров в поиске пользователей:</label>
        <input type="number" class="form-control" id="usersearch" name="sets[usersearch]" maxlength="2" value="{{ getInput('sets.usersearch', $settings['usersearch']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[usersearch]') }}</div>
    </div>

    <div class="form-group{{ hasError('sets[ipbanlist]') }}">
        <label for="ipbanlist">Листинг в IP-бан панеле:</label>
        <input type="number" class="form-control" id="ipbanlist" name="sets[ipbanlist]" maxlength="2" value="{{ getInput('sets.ipbanlist', $settings['ipbanlist']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[ipbanlist]') }}</div>
    </div>

    <div class="form-group{{ hasError('sets[loglist]') }}">
        <label for="loglist">Просмотр логов на страницу:</label>
        <input type="number" class="form-control" id="loglist" name="sets[loglist]" maxlength="2" value="{{ getInput('sets.loglist', $settings['loglist']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[loglist]') }}</div>
    </div>

    <div class="form-group{{ hasError('sets[blacklist]') }}">
        <label for="blacklist">Данных на страницу в черном списке:</label>
        <input type="number" class="form-control" id="blacklist" name="sets[blacklist]" maxlength="2" value="{{ getInput('sets.blacklist', $settings['blacklist']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[blacklist]') }}</div>
    </div>

    <div class="form-group{{ hasError('sets[reglist]') }}">
        <label for="reglist">Пользователей в списке ожидающих:</label>
        <input type="number" class="form-control" id="reglist" name="sets[reglist]" maxlength="2" value="{{ getInput('sets.reglist', $settings['reglist']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[reglist]') }}</div>
    </div>

    <div class="form-group{{ hasError('sets[listinvite]') }}">
        <label for="listinvite">Инвайтов в приглашениях:</label>
        <input type="number" class="form-control" id="listinvite" name="sets[listinvite]" maxlength="2" value="{{ getInput('sets.listinvite', $settings['listinvite']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[listinvite]') }}</div>
    </div>

    <div class="form-group{{ hasError('sets[wallpost]') }}">
        <label for="wallpost">Постов на стене сообщений:</label>
        <input type="number" class="form-control" id="wallpost" name="sets[wallpost]" maxlength="2" value="{{ getInput('sets.wallpost', $settings['wallpost']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[wallpost]') }}</div>
    </div>

    <div class="form-group{{ hasError('sets[wallmaxpost]') }}">
        <label for="wallmaxpost">Сохраняется постов на стене сообщений:</label>
        <input type="number" class="form-control" id="wallmaxpost" name="sets[wallmaxpost]" maxlength="4" value="{{ getInput('sets.wallmaxpost', $settings['wallmaxpost']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[wallmaxpost]') }}</div>
    </div>

    <div class="form-group{{ hasError('sets[loginauthlist]') }}">
        <label for="loginauthlist">История авторизаций:</label>
        <input type="number" class="form-control" id="loginauthlist" name="sets[loginauthlist]" maxlength="2" value="{{ getInput('sets.loginauthlist', $settings['loginauthlist']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[loginauthlist]') }}</div>
    </div>

    <div class="form-group{{ hasError('sets[listtransfers]') }}">
        <label for="listtransfers">Денежные операции:</label>
        <input type="number" class="form-control" id="listtransfers" name="sets[listtransfers]" maxlength="2" value="{{ getInput('sets.listtransfers', $settings['listtransfers']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[listtransfers]') }}</div>
    </div>

    <div class="form-group{{ hasError('sets[ratinglist]') }}">
        <label for="ratinglists">Голосов в истории рейтинга:</label>
        <input type="number" class="form-control" id="ratinglist" name="sets[ratinglist]" maxlength="2" value="{{ getInput('sets.ratinglist', $settings['ratinglist']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[ratinglist]') }}</div>
    </div>

    <button class="btn btn-primary">Сохранить</button>
</form>
