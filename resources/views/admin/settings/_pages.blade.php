@section('header')
    <h1>{{ __('settings.pages') }}</h1>
@stop

<form action="/admin/settings?act=pages" method="post">
    @csrf
    <div class="form-group{{ hasError('sets[userlist]') }}">
        <label for="userlist">{{ __('settings.users_per_page') }}:</label>
        <input type="number" class="form-control" id="userlist" name="sets[userlist]" maxlength="2" value="{{ getInput('sets.userlist', $settings['userlist']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[userlist]') }}</div>
    </div>

    <div class="form-group{{ hasError('sets[onlinelist]') }}">
        <label for="onlinelist">{{ __('settings.online_per_page') }}:</label>
        <input type="number" class="form-control" id="onlinelist" name="sets[onlinelist]" maxlength="2" value="{{ getInput('sets.onlinelist', $settings['onlinelist']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[onlinelist]') }}</div>
    </div>

    <div class="form-group{{ hasError('sets[avtorlist]') }}">
        <label for="avtorlist">{{ __('settings.authority_per_page') }}:</label>
        <input type="number" class="form-control" id="avtorlist" name="sets[avtorlist]" maxlength="2" value="{{ getInput('sets.avtorlist', $settings['avtorlist']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[avtorlist]') }}</div>
    </div>

    <div class="form-group{{ hasError('sets[banlist]') }}">
        <label for="banlist">{{ __('settings.banned_per_page') }}:</label>
        <input type="number" class="form-control" id="banlist" name="sets[banlist]" maxlength="2" value="{{ getInput('sets.banlist', $settings['banlist']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[banlist]') }}</div>
    </div>

    <div class="form-group{{ hasError('sets[listbanhist]') }}">
        <label for="listbanhist">{{ __('settings.history_ban_per_page') }}:</label>
        <input type="number" class="form-control" id="listbanhist" name="sets[listbanhist]" maxlength="2" value="{{ getInput('sets.listbanhist', $settings['listbanhist']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[listbanhist]') }}</div>
    </div>

    <div class="form-group{{ hasError('sets[usersearch]') }}">
        <label for="usersearch">{{ __('settings.search_users_per_page') }}:</label>
        <input type="number" class="form-control" id="usersearch" name="sets[usersearch]" maxlength="2" value="{{ getInput('sets.usersearch', $settings['usersearch']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[usersearch]') }}</div>
    </div>

    <div class="form-group{{ hasError('sets[ipbanlist]') }}">
        <label for="ipbanlist">{{ __('settings.ipban_per_page') }}:</label>
        <input type="number" class="form-control" id="ipbanlist" name="sets[ipbanlist]" maxlength="2" value="{{ getInput('sets.ipbanlist', $settings['ipbanlist']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[ipbanlist]') }}</div>
    </div>

    <div class="form-group{{ hasError('sets[loglist]') }}">
        <label for="loglist">{{ __('settings.logs_per_page') }}:</label>
        <input type="number" class="form-control" id="loglist" name="sets[loglist]" maxlength="2" value="{{ getInput('sets.loglist', $settings['loglist']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[loglist]') }}</div>
    </div>

    <div class="form-group{{ hasError('sets[blacklist]') }}">
        <label for="blacklist">{{ __('settings.blacklist_per_page') }}:</label>
        <input type="number" class="form-control" id="blacklist" name="sets[blacklist]" maxlength="2" value="{{ getInput('sets.blacklist', $settings['blacklist']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[blacklist]') }}</div>
    </div>

    <div class="form-group{{ hasError('sets[reglist]') }}">
        <label for="reglist">{{ __('settings.reglist_per_page') }}:</label>
        <input type="number" class="form-control" id="reglist" name="sets[reglist]" maxlength="2" value="{{ getInput('sets.reglist', $settings['reglist']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[reglist]') }}</div>
    </div>

    <div class="form-group{{ hasError('sets[listinvite]') }}">
        <label for="listinvite">{{ __('settings.invites_per_page') }}:</label>
        <input type="number" class="form-control" id="listinvite" name="sets[listinvite]" maxlength="2" value="{{ getInput('sets.listinvite', $settings['listinvite']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[listinvite]') }}</div>
    </div>

    <div class="form-group{{ hasError('sets[wallpost]') }}">
        <label for="wallpost">{{ __('settings.walls_per_page') }}:</label>
        <input type="number" class="form-control" id="wallpost" name="sets[wallpost]" maxlength="2" value="{{ getInput('sets.wallpost', $settings['wallpost']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[wallpost]') }}</div>
    </div>

    <div class="form-group{{ hasError('sets[wallmaxpost]') }}">
        <label for="wallmaxpost">{{ __('settings.walls_max_posts') }}:</label>
        <input type="number" class="form-control" id="wallmaxpost" name="sets[wallmaxpost]" maxlength="4" value="{{ getInput('sets.wallmaxpost', $settings['wallmaxpost']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[wallmaxpost]') }}</div>
    </div>

    <div class="form-group{{ hasError('sets[loginauthlist]') }}">
        <label for="loginauthlist">{{ __('settings.history_login_per_page') }}:</label>
        <input type="number" class="form-control" id="loginauthlist" name="sets[loginauthlist]" maxlength="2" value="{{ getInput('sets.loginauthlist', $settings['loginauthlist']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[loginauthlist]') }}</div>
    </div>

    <div class="form-group{{ hasError('sets[listtransfers]') }}">
        <label for="listtransfers">{{ __('settings.transfers_per_page') }}:</label>
        <input type="number" class="form-control" id="listtransfers" name="sets[listtransfers]" maxlength="2" value="{{ getInput('sets.listtransfers', $settings['listtransfers']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[listtransfers]') }}</div>
    </div>

    <div class="form-group{{ hasError('sets[ratinglist]') }}">
        <label for="ratinglists">{{ __('settings.ratinglist_per_page') }}:</label>
        <input type="number" class="form-control" id="ratinglist" name="sets[ratinglist]" maxlength="2" value="{{ getInput('sets.ratinglist', $settings['ratinglist']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[ratinglist]') }}</div>
    </div>

    <div class="form-group{{ hasError('sets[allvotes]') }}">
        <label for="allvotes">{{ __('settings.votes_per_page') }}:</label>
        <input type="number" class="form-control" id="allvotes" name="sets[allvotes]" maxlength="2" value="{{ getInput('sets.allvotes', $settings['allvotes']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[allvotes]') }}</div>
    </div>

    <button class="btn btn-primary">{{ __('main.save') }}</button>
</form>
