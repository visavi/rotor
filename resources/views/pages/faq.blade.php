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

    @if (setting('comment_point') || setting('comment_money'))
        {{ __('pages.faq_money_comment') }} -
        <b>{{ plural((int) setting('comment_point'), setting('scorename')) }}</b> и <b>{{ plural((int) setting('comment_money'), setting('moneyname')) }}</b><br>
    @endif

    @if (setting('guestbook_point') || setting('guestbook_money'))
        {{ __('pages.faq_money_guestbook') }} -
        <b>{{ plural((int) setting('guestbook_point'), setting('scorename')) }}</b> и <b>{{ plural((int) setting('guestbook_money'), setting('moneyname')) }}</b><br>
    @endif

    @if (setting('down_point') || setting('down_money'))
        {{ __('pages.faq_money_down') }} -
        <b>{{ plural((int) setting('down_point'), setting('scorename')) }}</b> и <b>{{ plural((int) setting('down_money'), setting('moneyname')) }}</b><br>
    @endif

    @if (setting('blog_point') || setting('blog_money'))
        {{ __('pages.faq_money_blog') }} -
        <b>{{ plural((int) setting('blog_point'), setting('scorename')) }}</b> и <b>{{ plural((int) setting('blog_money'), setting('moneyname')) }}</b><br>
    @endif

    @if (setting('forum_point') || setting('forum_money'))
        {{ __('pages.faq_money_forum') }} -
        <b>{{ plural((int) setting('forum_point'), setting('scorename')) }}</b> и <b>{{ plural((int) setting('forum_money'), setting('moneyname')) }}</b><br>
    @endif

    @if (setting('registermoney'))
        {{ __('pages.faq_money_register') }} -
        <b>{{ plural(setting('registermoney'), setting('moneyname')) }}</b><br>
    @endif

    @if (setting('bonusmoney'))
        {{ __('pages.faq_money_bonus') }} -
        <b>{{ plural(setting('bonusmoney'), setting('moneyname')) }}</b><br>
    @endif

    <br>

    {{ __('pages.faq_active_text9') }}<br><br>

    {!! __('pages.how_is_registration') !!}<br>
    {!! __('pages.why_do_you_need_status_and_reputation') !!}<br>
    {!! __('pages.what_will_give_me_status') !!}<br>
    {!! __('pages.how_can_i_help_site') !!}<br>
    {!! __('pages.did_not_find_answer') !!}<br>
@stop
