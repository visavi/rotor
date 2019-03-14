@extends('layout')

@section('title')
    {{ trans('pages.faq') }}
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item active">{{ trans('pages.faq') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    {!! trans('pages.why_register') !!}<br>

    {{ trans('pages.faq_active') }}:<br>

    @if (setting('rekuserpoint'))
        <b>{{ plural(setting('rekuserpoint'), setting('scorename')) }}</b> - {{ trans('pages.faq_active_text1') }}<br>
    @endif

    @if (setting('privatprotect'))
        <b>{{ plural(setting('privatprotect'), setting('scorename')) }}</b> - {{ trans('pages.faq_active_text2') }}<br>
    @endif

    @if (setting('addofferspoint'))
        <b>{{ plural(setting('addofferspoint'), setting('scorename')) }}</b> - {{ trans('pages.faq_active_text3') }}<br>
    @endif

    @if (setting('forumloadpoints'))
        <b>{{ plural(setting('forumloadpoints'), setting('scorename')) }}</b> - {{ trans('pages.faq_active_text4') }}<br>
    @endif

    @if (setting('sendmoneypoint'))
        <b>{{ plural(setting('sendmoneypoint'), setting('scorename')) }}</b> - {{ trans('pages.faq_active_text5') }}<br>
    @endif

    @if (setting('editratingpoint'))
        <b>{{ plural(setting('editratingpoint'), setting('scorename')) }}</b> - {{ trans('pages.faq_active_text6') }}<br>
    @endif

    @if (setting('editforumpoint'))
        <b>{{ plural(setting('editforumpoint'), setting('scorename')) }}</b> - {{ trans('pages.faq_active_text7') }}<br>
    @endif

    @if (setting('advertpoint'))
        <b>{{ plural(setting('advertpoint'), setting('scorename')) }}</b> - {{ trans('pages.faq_active_text8') }}<br>
    @endif
    {{ trans('pages.faq_active_text9') }}<br><br>

    {!! trans('pages.how_is_registration') !!}<br>
    {!! trans('pages.why_do_you_need_status_and_reputation') !!}<br>
    {!! trans('pages.what_will_give_me_status') !!}<br>
    {!! trans('pages.how_can_i_help_site') !!}<br>
    {!! trans('pages.did_not_find_answer') !!}<br>
@stop
