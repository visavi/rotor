@section('header')
    <h1>{{ __('settings.pages') }}</h1>
@stop

<form method="post">
    @csrf
    <div class="mb-3{{ hasError('sets[userlist]') }}">
        <label for="userlist" class="form-label">{{ __('settings.users_per_page') }}:</label>
        <input type="number" class="form-control" id="userlist" name="sets[userlist]" maxlength="2" value="{{ getInput('sets.userlist', $settings['userlist']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[userlist]') }}</div>
    </div>

    <div class="mb-3{{ hasError('sets[onlinelist]') }}">
        <label for="onlinelist" class="form-label">{{ __('settings.online_per_page') }}:</label>
        <input type="number" class="form-control" id="onlinelist" name="sets[onlinelist]" maxlength="2" value="{{ getInput('sets.onlinelist', $settings['onlinelist']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[onlinelist]') }}</div>
    </div>

    <div class="mb-3{{ hasError('sets[avtorlist]') }}">
        <label for="avtorlist" class="form-label">{{ __('settings.authority_per_page') }}:</label>
        <input type="number" class="form-control" id="avtorlist" name="sets[avtorlist]" maxlength="2" value="{{ getInput('sets.avtorlist', $settings['avtorlist']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[avtorlist]') }}</div>
    </div>

    <div class="mb-3{{ hasError('sets[banlist]') }}">
        <label for="banlist" class="form-label">{{ __('settings.banned_per_page') }}:</label>
        <input type="number" class="form-control" id="banlist" name="sets[banlist]" maxlength="2" value="{{ getInput('sets.banlist', $settings['banlist']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[banlist]') }}</div>
    </div>

    <div class="mb-3{{ hasError('sets[listbanhist]') }}">
        <label for="listbanhist" class="form-label">{{ __('settings.history_ban_per_page') }}:</label>
        <input type="number" class="form-control" id="listbanhist" name="sets[listbanhist]" maxlength="2" value="{{ getInput('sets.listbanhist', $settings['listbanhist']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[listbanhist]') }}</div>
    </div>

    <div class="mb-3{{ hasError('sets[usersearch]') }}">
        <label for="usersearch" class="form-label">{{ __('settings.search_users_per_page') }}:</label>
        <input type="number" class="form-control" id="usersearch" name="sets[usersearch]" maxlength="2" value="{{ getInput('sets.usersearch', $settings['usersearch']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[usersearch]') }}</div>
    </div>

    <div class="mb-3{{ hasError('sets[ipbanlist]') }}">
        <label for="ipbanlist" class="form-label">{{ __('settings.ipban_per_page') }}:</label>
        <input type="number" class="form-control" id="ipbanlist" name="sets[ipbanlist]" maxlength="2" value="{{ getInput('sets.ipbanlist', $settings['ipbanlist']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[ipbanlist]') }}</div>
    </div>

    <div class="mb-3{{ hasError('sets[loglist]') }}">
        <label for="loglist" class="form-label">{{ __('settings.logs_per_page') }}:</label>
        <input type="number" class="form-control" id="loglist" name="sets[loglist]" maxlength="2" value="{{ getInput('sets.loglist', $settings['loglist']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[loglist]') }}</div>
    </div>

    <div class="mb-3{{ hasError('sets[blacklist]') }}">
        <label for="blacklist" class="form-label">{{ __('settings.blacklist_per_page') }}:</label>
        <input type="number" class="form-control" id="blacklist" name="sets[blacklist]" maxlength="2" value="{{ getInput('sets.blacklist', $settings['blacklist']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[blacklist]') }}</div>
    </div>

    <div class="mb-3{{ hasError('sets[reglist]') }}">
        <label for="reglist" class="form-label">{{ __('settings.reglist_per_page') }}:</label>
        <input type="number" class="form-control" id="reglist" name="sets[reglist]" maxlength="2" value="{{ getInput('sets.reglist', $settings['reglist']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[reglist]') }}</div>
    </div>

    <div class="mb-3{{ hasError('sets[listinvite]') }}">
        <label for="listinvite" class="form-label">{{ __('settings.invites_per_page') }}:</label>
        <input type="number" class="form-control" id="listinvite" name="sets[listinvite]" maxlength="2" value="{{ getInput('sets.listinvite', $settings['listinvite']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[listinvite]') }}</div>
    </div>

    <div class="mb-3{{ hasError('sets[wallpost]') }}">
        <label for="wallpost" class="form-label">{{ __('settings.walls_per_page') }}:</label>
        <input type="number" class="form-control" id="wallpost" name="sets[wallpost]" maxlength="2" value="{{ getInput('sets.wallpost', $settings['wallpost']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[wallpost]') }}</div>
    </div>

    <div class="mb-3{{ hasError('sets[loginauthlist]') }}">
        <label for="loginauthlist" class="form-label">{{ __('settings.history_login_per_page') }}:</label>
        <input type="number" class="form-control" id="loginauthlist" name="sets[loginauthlist]" maxlength="2" value="{{ getInput('sets.loginauthlist', $settings['loginauthlist']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[loginauthlist]') }}</div>
    </div>

    <div class="mb-3{{ hasError('sets[listtransfers]') }}">
        <label for="listtransfers" class="form-label">{{ __('settings.transfers_per_page') }}:</label>
        <input type="number" class="form-control" id="listtransfers" name="sets[listtransfers]" maxlength="2" value="{{ getInput('sets.listtransfers', $settings['listtransfers']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[listtransfers]') }}</div>
    </div>

    <div class="mb-3{{ hasError('sets[ratinglist]') }}">
        <label for="ratinglists" class="form-label">{{ __('settings.ratinglist_per_page') }}:</label>
        <input type="number" class="form-control" id="ratinglist" name="sets[ratinglist]" maxlength="2" value="{{ getInput('sets.ratinglist', $settings['ratinglist']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[ratinglist]') }}</div>
    </div>

    <div class="mb-3{{ hasError('sets[allvotes]') }}">
        <label for="allvotes" class="form-label">{{ __('settings.votes_per_page') }}:</label>
        <input type="number" class="form-control" id="allvotes" name="sets[allvotes]" maxlength="2" value="{{ getInput('sets.allvotes', $settings['allvotes']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[allvotes]') }}</div>
    </div>

    <button class="btn btn-primary">{{ __('main.save') }}</button>
</form>
