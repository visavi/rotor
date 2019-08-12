@section('header')
    <h1>{{ trans('settings.pages') }}</h1>
@stop

<form action="/admin/settings?act=pages" method="post">
    @csrf
    <div class="form-group{{ hasError('sets[userlist]') }}">
        <label for="userlist">{{ trans('settings.users_per_page') }}:</label>
        <input type="number" class="form-control" id="userlist" name="sets[userlist]" maxlength="2" value="{{ getInput('sets.userlist', $settings['userlist']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[userlist]') }}</div>
    </div>

    <div class="form-group{{ hasError('sets[onlinelist]') }}">
        <label for="onlinelist">{{ trans('settings.online_per_page') }}:</label>
        <input type="number" class="form-control" id="onlinelist" name="sets[onlinelist]" maxlength="2" value="{{ getInput('sets.onlinelist', $settings['onlinelist']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[onlinelist]') }}</div>
    </div>

    <div class="form-group{{ hasError('sets[avtorlist]') }}">
        <label for="avtorlist">{{ trans('settings.authority_per_page') }}:</label>
        <input type="number" class="form-control" id="avtorlist" name="sets[avtorlist]" maxlength="2" value="{{ getInput('sets.avtorlist', $settings['avtorlist']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[avtorlist]') }}</div>
    </div>

    <div class="form-group{{ hasError('sets[banlist]') }}">
        <label for="banlist">{{ trans('settings.banned_per_page') }}:</label>
        <input type="number" class="form-control" id="banlist" name="sets[banlist]" maxlength="2" value="{{ getInput('sets.banlist', $settings['banlist']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[banlist]') }}</div>
    </div>

    <div class="form-group{{ hasError('sets[listbanhist]') }}">
        <label for="listbanhist">{{ trans('settings.history_ban_per_page') }}:</label>
        <input type="number" class="form-control" id="listbanhist" name="sets[listbanhist]" maxlength="2" value="{{ getInput('sets.listbanhist', $settings['listbanhist']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[listbanhist]') }}</div>
    </div>

    <div class="form-group{{ hasError('sets[usersearch]') }}">
        <label for="usersearch">{{ trans('settings.search_users_per_page') }}:</label>
        <input type="number" class="form-control" id="usersearch" name="sets[usersearch]" maxlength="2" value="{{ getInput('sets.usersearch', $settings['usersearch']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[usersearch]') }}</div>
    </div>

    <div class="form-group{{ hasError('sets[ipbanlist]') }}">
        <label for="ipbanlist">{{ trans('settings.ipban_per_page') }}:</label>
        <input type="number" class="form-control" id="ipbanlist" name="sets[ipbanlist]" maxlength="2" value="{{ getInput('sets.ipbanlist', $settings['ipbanlist']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[ipbanlist]') }}</div>
    </div>

    <div class="form-group{{ hasError('sets[loglist]') }}">
        <label for="loglist">{{ trans('settings.logs_per_page') }}:</label>
        <input type="number" class="form-control" id="loglist" name="sets[loglist]" maxlength="2" value="{{ getInput('sets.loglist', $settings['loglist']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[loglist]') }}</div>
    </div>

    <div class="form-group{{ hasError('sets[blacklist]') }}">
        <label for="blacklist">{{ trans('settings.blacklist_per_page') }}:</label>
        <input type="number" class="form-control" id="blacklist" name="sets[blacklist]" maxlength="2" value="{{ getInput('sets.blacklist', $settings['blacklist']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[blacklist]') }}</div>
    </div>

    <div class="form-group{{ hasError('sets[reglist]') }}">
        <label for="reglist">{{ trans('settings.reglist_per_page') }}:</label>
        <input type="number" class="form-control" id="reglist" name="sets[reglist]" maxlength="2" value="{{ getInput('sets.reglist', $settings['reglist']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[reglist]') }}</div>
    </div>

    <div class="form-group{{ hasError('sets[listinvite]') }}">
        <label for="listinvite">{{ trans('settings.invites_per_page') }}:</label>
        <input type="number" class="form-control" id="listinvite" name="sets[listinvite]" maxlength="2" value="{{ getInput('sets.listinvite', $settings['listinvite']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[listinvite]') }}</div>
    </div>

    <div class="form-group{{ hasError('sets[wallpost]') }}">
        <label for="wallpost">{{ trans('settings.walls_per_page') }}:</label>
        <input type="number" class="form-control" id="wallpost" name="sets[wallpost]" maxlength="2" value="{{ getInput('sets.wallpost', $settings['wallpost']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[wallpost]') }}</div>
    </div>

    <div class="form-group{{ hasError('sets[wallmaxpost]') }}">
        <label for="wallmaxpost">{{ trans('settings.walls_max_posts') }}:</label>
        <input type="number" class="form-control" id="wallmaxpost" name="sets[wallmaxpost]" maxlength="4" value="{{ getInput('sets.wallmaxpost', $settings['wallmaxpost']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[wallmaxpost]') }}</div>
    </div>

    <div class="form-group{{ hasError('sets[loginauthlist]') }}">
        <label for="loginauthlist">{{ trans('settings.history_login_per_page') }}:</label>
        <input type="number" class="form-control" id="loginauthlist" name="sets[loginauthlist]" maxlength="2" value="{{ getInput('sets.loginauthlist', $settings['loginauthlist']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[loginauthlist]') }}</div>
    </div>

    <div class="form-group{{ hasError('sets[listtransfers]') }}">
        <label for="listtransfers">{{ trans('settings.transfers_per_page') }}:</label>
        <input type="number" class="form-control" id="listtransfers" name="sets[listtransfers]" maxlength="2" value="{{ getInput('sets.listtransfers', $settings['listtransfers']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[listtransfers]') }}</div>
    </div>

    <div class="form-group{{ hasError('sets[ratinglist]') }}">
        <label for="ratinglists">{{ trans('settings.ratinglist_per_page') }}:</label>
        <input type="number" class="form-control" id="ratinglist" name="sets[ratinglist]" maxlength="2" value="{{ getInput('sets.ratinglist', $settings['ratinglist']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[ratinglist]') }}</div>
    </div>

    <div class="form-group{{ hasError('sets[allvotes]') }}">
        <label for="allvotes">{{ trans('settings.votes_per_page') }}:</label>
        <input type="number" class="form-control" id="allvotes" name="sets[allvotes]" maxlength="2" value="{{ getInput('sets.allvotes', $settings['allvotes']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[allvotes]') }}</div>
    </div>

    <button class="btn btn-primary">{{ trans('main.save') }}</button>
</form>
