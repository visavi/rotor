@extends('layout')

@section('title', __('pages.faq'))

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item active">{{ __('pages.faq') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    {!! __('pages.why_register') !!}<br>

    <h3 class="my-3">{{ __('pages.faq_active') }}:</h3>

    @if (setting('rekuserpoint'))
        <b>{{ plural(setting('rekuserpoint'), setting('scorename')) }}</b> - {{ __('pages.faq_active_text1') }}<br>
    @endif

    @if (setting('privatprotect'))
        <b>{{ plural(setting('privatprotect'), setting('scorename')) }}</b> - {{ __('pages.faq_active_text2') }}<br>
    @endif

    @if (setting('addofferspoint'))
        <b>{{ plural(setting('addofferspoint'), setting('scorename')) }}</b> - {{ __('pages.faq_active_text3') }}<br>
    @endif

    @if (setting('sendmoneypoint'))
        <b>{{ plural(setting('sendmoneypoint'), setting('scorename')) }}</b> - {{ __('pages.faq_active_text5') }}<br>
    @endif

    @if (setting('editratingpoint'))
        <b>{{ plural(setting('editratingpoint'), setting('scorename')) }}</b> - {{ __('pages.faq_active_text6') }}<br>
    @endif

    @if (setting('editforumpoint'))
        <b>{{ plural(setting('editforumpoint'), setting('scorename')) }}</b> - {{ __('pages.faq_active_text7') }}<br>
    @endif

    @if (setting('advertpoint'))
        <b>{{ plural(setting('advertpoint'), setting('scorename')) }}</b> - {{ __('pages.faq_active_text8') }}<br>
    @endif

    @if (setting('editcolorpoint'))
        <b>{{ plural(setting('editcolorpoint'), setting('scorename')) }}</b> - {{ __('pages.faq_active_text4') }}<br>
    @endif

    @if (setting('editstatuspoint'))
        <b>{{ plural(setting('editstatuspoint'), setting('scorename')) }}</b> - {{ __('pages.faq_active_text10') }}<br>
    @endif

    <h3 class="my-3">{{ __('pages.faq_money') }}</h3>

    {{ __('pages.faq_money_comment') }} -
    <b>{{ plural(setting('comment_point'), setting('scorename')) }}</b> и <b>{{ plural(setting('comment_money'), setting('moneyname')) }}</b><br>

    {{ __('pages.faq_money_guestbook') }} -
    <b>{{ plural(setting('guestbook_point'), setting('scorename')) }}</b> и <b>{{ plural(setting('guestbook_money'), setting('moneyname')) }}</b><br>

    {{ __('pages.faq_money_down') }} -
    <b>{{ plural(setting('down_point'), setting('scorename')) }}</b> и <b>{{ plural(setting('down_money'), setting('moneyname')) }}</b><br>

    {{ __('pages.faq_money_blog') }} -
    <b>{{ plural(setting('blog_point'), setting('scorename')) }}</b> и <b>{{ plural(setting('blog_money'), setting('moneyname')) }}</b><br>

    {{ __('pages.faq_money_forum') }} -
    <b>{{ plural(setting('forum_point'), setting('scorename')) }}</b> и <b>{{ plural(setting('forum_money'), setting('moneyname')) }}</b><br>

    {{ __('pages.faq_money_register') }} -
    <b>{{ plural(setting('registermoney'), setting('moneyname')) }}</b><br>

    {{ __('pages.faq_money_bonus') }} -
    <b>{{ plural(setting('bonusmoney'), setting('moneyname')) }}</b><br>

    <br>

    {{ __('pages.faq_active_text9') }}<br><br>

    {!! __('pages.how_is_registration') !!}<br>
    {!! __('pages.why_do_you_need_status_and_reputation') !!}<br>
    {!! __('pages.what_will_give_me_status') !!}<br>
    {!! __('pages.how_can_i_help_site') !!}<br>
    {!! __('pages.did_not_find_answer') !!}<br>
@stop
