@section('title', __('index.menu'))

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item active">{{ __('index.menu') }}</li>
        </ol>
    </nav>
@stop

<div class="section mb-3 shadow">
    <div class="section-title">
        <i class="fa fa-envelope fa-lg text-muted"></i> {{ __('index.mail_contact') }}
    </div>
    <div class="section-body">
        <i class="far fa-circle fa-lg text-muted"></i> <a href="/messages">{{ __('index.messages') }}</a> ({{ getUser()->getCountMessages() }})<br>
        <i class="far fa-circle fa-lg text-muted"></i> <a href="/contacts">{{ __('index.contacts') }}</a> ({{ getUser()->getCountContact() }})<br>
        <i class="far fa-circle fa-lg text-muted"></i> <a href="/ignores">{{ __('index.ignores') }}</a> ({{ getUser()->getCountIgnore() }})<br>
    </div>
</div>

<div class="section mb-3 shadow">
    <div class="section-title">
        <i class="fa fa-wrench fa-lg text-muted"></i> {{ __('index.profile_settings') }}
    </div>
    <div class="section-body">
        <i class="far fa-circle fa-lg text-muted"></i> <a href="/users/{{ getUser('login') }}">{{ __('index.my_account') }}</a><br>
        <i class="far fa-circle fa-lg text-muted"></i> <a href="/profile">{{ __('index.my_profile') }}</a><br>
        <i class="far fa-circle fa-lg text-muted"></i> <a href="/accounts">{{ __('index.my_details') }}</a><br>
        <i class="far fa-circle fa-lg text-muted"></i> <a href="/settings">{{ __('index.my_settings') }}</a><br>
        <i class="far fa-circle fa-lg text-muted"></i> <a href="/socials">{{ __('index.social_networks') }}</a><br>
    </div>
</div>

<div class="section mb-3 shadow">
    <div class="section-title">
        <i class="fa fa-star fa-lg text-muted"></i> {{ __('index.activity') }}
    </div>
    <div class="section-body">
        <i class="far fa-circle fa-lg text-muted"></i> <a href="/walls/{{ getUser('login') }}">{{ __('index.my_wall') }}</a> ({{ getUser()->getCountWall() }})<br>
        <i class="far fa-circle fa-lg text-muted"></i> <a href="/notebooks">{{ __('index.notebook') }}</a><br>
        <i class="far fa-circle fa-lg text-muted"></i> <a href="/adverts">{{ __('index.advertising') }}</a><br>
        <i class="far fa-circle fa-lg text-muted"></i> <a href="/ratings/{{ getUser('login') }}">{{ __('index.reputation_history') }}</a><br>
        <i class="far fa-circle fa-lg text-muted"></i> <a href="/authlogs">{{ __('index.auth_history') }}</a><br>
        <i class="far fa-circle fa-lg text-muted"></i> <a href="/transfers">{{ __('index.money_transfer') }}</a><br>
    </div>
</div>

<div class="section mb-3 shadow">
    <div class="section-title">
        <i class="fa fa-sign-out-alt fa-lg text-muted"></i> {{ __('index.logout') }}
    </div>
    <div class="section-body">
        <i class="far fa-circle fa-lg text-muted"></i> <a href="/logout?_token={{ csrf_token() }}">{{ __('index.logout') }}</a><br>
    </div>
</div>
