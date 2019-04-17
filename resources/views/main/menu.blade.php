@section('title')
    {{ trans('index.menu') }}
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item active">{{ trans('index.menu') }}</li>
        </ol>
    </nav>
@stop

<div class="b"><i class="fa fa-envelope fa-lg text-muted"></i> <b>{{ trans('index.mail_contact') }}</b></div>
<i class="far fa-circle fa-lg text-muted"></i> <a href="/messages">{{ trans('index.messages') }}</a> ({{ getUser()->getCountMessages() }})<br>
<i class="far fa-circle fa-lg text-muted"></i> <a href="/contacts">{{ trans('index.contacts') }}</a> ({{ getUser()->getCountContact() }})<br>
<i class="far fa-circle fa-lg text-muted"></i> <a href="/ignores">{{ trans('index.ignores') }}</a> ({{ getUser()->getCountIgnore() }})<br>

<div class="b"><i class="fa fa-wrench fa-lg text-muted"></i> <b>{{ trans('index.profile_settings') }}</b></div>
<i class="far fa-circle fa-lg text-muted"></i> <a href="/users/{{ getUser('login') }}">{{ trans('index.my_account') }}</a><br>
<i class="far fa-circle fa-lg text-muted"></i> <a href="/profile">{{ trans('index.my_profile') }}</a><br>
<i class="far fa-circle fa-lg text-muted"></i> <a href="/accounts">{{ trans('index.my_data') }}</a><br>
<i class="far fa-circle fa-lg text-muted"></i> <a href="/settings">{{ trans('index.my_settings') }}</a><br>
<i class="far fa-circle fa-lg text-muted"></i> <a href="/socials">{{ trans('index.social_networks') }}</a><br>

<div class="b"><i class="fa fa-star fa-lg text-muted"></i> <b>{{ trans('index.activity') }}</b></div>
<i class="far fa-circle fa-lg text-muted"></i> <a href="/walls/{{ getUser('login') }}">{{ trans('index.my_wall') }}</a> ({{ getUser()->getCountWall() }})<br>
<i class="far fa-circle fa-lg text-muted"></i> <a href="/notebooks">{{ trans('index.notebook') }}</a><br>
<i class="far fa-circle fa-lg text-muted"></i> <a href="/adverts">{{ trans('index.advertising') }}</a><br>
<i class="far fa-circle fa-lg text-muted"></i> <a href="/ratings/{{ getUser('login') }}">{{ trans('index.reputation_history') }}</a><br>
<i class="far fa-circle fa-lg text-muted"></i> <a href="/authlogs">{{ trans('index.auth_history') }}</a><br>
<i class="far fa-circle fa-lg text-muted"></i> <a href="/transfers">{{ trans('index.money_transfer') }}</a><br>

<div class="b"><i class="fa fa-sign-out-alt fa-lg text-muted"></i> <b>{{ trans('index.logout') }}</b></div>
<i class="far fa-circle fa-lg text-muted"></i> <a href="/logout?token={{ $_SESSION['token'] }}">{{ trans('index.logout') }}</a><br>
