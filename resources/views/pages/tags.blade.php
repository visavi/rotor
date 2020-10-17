@extends('layout')

@section('title', __('pages.tags'))

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item active">{{ __('pages.tags') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    {{ __('pages.tags_text') }}:<br><br>

    <i class="fa fa-bold"></i> [b]{!! bbCode('[b]' . __('pages.bold') . '[/b]') !!}[/b]<br>
    <i class="fa fa-italic"></i> [i]{!! bbCode('[i]' . __('pages.italic') . '[/i]') !!}[/i]<br>
    <i class="fa fa-underline"></i> [u]{!! bbCode('[u]' . __('pages.underline') . '[/u]') !!}[/u]<br>
    <i class="fa fa-strikethrough"></i> [s]{!! bbCode('[s]' . __('pages.strike') . '[/s]') !!}[/s]<br><br>

    <i class="fa fa-font"></i> {{ __('pages.font_size') }}<br>
    <i class="fa fa-font"></i> [size=1]{!! bbCode('[size=1]' . __('pages.small_font') . '[/size]') !!}[/size]<br>
    <i class="fa fa-font"></i> [size=3]{!! bbCode('[size=3]' . __('pages.medium_font') . '[/size]') !!}[/size]<br>
    <i class="fa fa-font"></i> [size=5]{!! bbCode('[size=5]' . __('pages.big_font') . '[/size]') !!}[/size]<br><br>

    <i class="fa fa-th"></i> {{ __('pages.font_color') }}<br>
    <i class="fa fa-th"></i> [color=#ff0000]{!! bbCode('[color=#ff0000]' . __('pages.red_font') . '[/color]') !!}[/color]<br>
    <i class="fa fa-th"></i> [color=#00cc00]{!! bbCode('[color=#00cc00]' . __('pages.green_font') . '[/color]') !!}[/color]<br>
    <i class="fa fa-th"></i> [color=#00ffff]{!! bbCode('[color=#00ffff]' . __('pages.blue_font') . '[/color]') !!}[/color]<br><br>

    <i class="fa fa-link"></i> {{ __('pages.link') }} http://site.com<br>
    <i class="fa fa-link"></i> {{ __('pages.link_text') }}: [url=http://site.com]site.com[/url]<br>
    <i class="fa fa-link"></i> {{ __('pages.link_short') }}: [url]http://site.com[/url]<br><br>

    <i class="fa fa-image"></i> [img]{{ __('pages.image') }}[/img]<br>{!! bbCode('[img]/assets/img/images/logo.png[/img]') !!}<br>
    <i class="fab fa-youtube"></i> [youtube]{{ __('pages.video') }}[/youtube]<br>{!! bbCode('[youtube]https://www.youtube.com/watch?v=yf_YWiqqv34[/youtube]') !!}<br>

    <i class="fa fa-align-center"></i> [center]{{ __('pages.center') }}[/center]{!! bbCode('[center]' . __('pages.center') . '[/center]') !!}<br>
    <i class="fa fa-list-ul"></i> [list]{{ __('pages.unorderedlist') }}[/list]{!! bbCode('[list]' . __('pages.unorderedlist') . '[/list]') !!}<br>
    <i class="fa fa-list-ol"></i> [list=1]{{ __('pages.orderedlist') }}[/list]{!! bbCode('[list=1]' . __('pages.orderedlist') . '[/list]') !!}<br>

    <i class="fa fa-text-height"></i> [spoiler]{{ __('pages.spoiler_text') }}[/spoiler]{!! bbCode('[spoiler]' . __('pages.spoiler_text') . '[/spoiler]') !!}<br>
    <i class="fa fa-text-height"></i> [spoiler={{ __('pages.spoiler_title') }}]{{ __('pages.spoiler_text') }}[/spoiler]{!! bbCode('[spoiler=' . __('pages.spoiler_title') . ']' . __('pages.spoiler_text') . '[/spoiler]') !!}<br>

    <i class="fa fa-eye-slash"></i> [hide]{{ __('pages.hide_text') }}[/hide]{!! bbCode('[hide]' . __('pages.hide_text') . '[/hide]') !!}<br>
    <i class="fa fa-quote-right"></i> [quote]{{ __('pages.quote') }}[/quote]{!! bbCode('[quote]' . __('pages.quote') . '[/quote]') !!}<br>
    <i class="fa fa-quote-right"></i> [quote={{ __('pages.quote_author') }}]{{ __('pages.quote') }}[/quote]{!! bbCode('[quote=' . __('pages.quote_author')  . ']' . __('pages.quote') . '[/quote]') !!}<br>

    <i class="fa fa-code"></i> [code]{{ __('pages.code') }}[/code]{!! bbCode('[code]' . __('pages.code') . '[/code]') !!}<br>
    <i class="fa fa-cut"></i> [cut] - {{ __('pages.cutpage') }}<br>
    <i class="fa fa-eraser"></i> {{ __('pages.clean_text') }}<br>
    <i class="fa fa-smile"></i> {{ __('pages.sticker') }}<br>
    <i class="fa fa-check-square"></i> {{ __('pages.preview') }}<br><br>
@stop
