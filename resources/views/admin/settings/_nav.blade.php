<button class="btn btn-outline-secondary w-100 mb-3 d-md-none" type="button" data-bs-toggle="collapse" data-bs-target="#settingsNav" aria-controls="settingsNav" aria-expanded="false">
    <i class="fas fa-bars"></i> {{ __('index.site_settings') }}
</button>

<div class="collapse d-md-flex flex-column nav nav-pills settings-nav js-settings-nav" id="settingsNav">
    @if (getUser()?->isAdmin(App\Models\User::BOSS))
        @foreach (App\Models\Setting::getActions() as $action)
            <a class="nav-link" href="/admin/settings?act={{ $action }}" id="{{ $action }}">{{ __('settings.' . $action) }}</a>
        @endforeach
    @endif

    @if (App\Support\Hook::has('adminSettingsNav'))
        <div class="text-muted small fw-semibold px-3 pt-3 pb-1">{{ __('index.modules') }}</div>
        @hook('adminSettingsNav')
    @endif
</div>
