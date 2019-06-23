@extends('layout')

@section('title')
    {{ trans('pages.tags') }}
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item active">{{ trans('pages.tags') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    {{ trans('pages.tags_text') }}:<br><br>

    <i class="fa fa-bold"></i> [b]{!! bbCode('[b]' . trans('pages.bold') . '[/b]') !!}[/b]<br>
    <i class="fa fa-italic"></i> [i]{!! bbCode('[i]' . trans('pages.italic') . '[/i]') !!}[/i]<br>
    <i class="fa fa-underline"></i> [u]{!! bbCode('[u]' . trans('pages.underline') . '[/u]') !!}[/u]<br>
    <i class="fa fa-strikethrough"></i> [s]{!! bbCode('[s]' . trans('pages.strike') . '[/s]') !!}[/s]<br><br>

    <i class="fa fa-font"></i> {{ trans('pages.font_size') }}<br>
    <i class="fa fa-font"></i> [size=1]{!! bbCode('[size=1]' . trans('pages.small_font') . '[/size]') !!}[/size]<br>
    <i class="fa fa-font"></i> [size=3]{!! bbCode('[size=3]' . trans('pages.medium_font') . '[/size]') !!}[/size]<br>
    <i class="fa fa-font"></i> [size=5]{!! bbCode('[size=5]' . trans('pages.big_font') . '[/size]') !!}[/size]<br><br>

    <i class="fa fa-th"></i> {{ trans('pages.font_color') }}<br>
    <i class="fa fa-th"></i> [color=#ff0000]{!! bbCode('[color=#ff0000]' . trans('pages.red_font') . '[/color]') !!}[/color]<br>
    <i class="fa fa-th"></i> [color=#00cc00]{!! bbCode('[color=#00cc00]' . trans('pages.green_font') . '[/color]') !!}[/color]<br>
    <i class="fa fa-th"></i> [color=#00ffff]{!! bbCode('[color=#00ffff]' . trans('pages.blue_font') . '[/color]') !!}[/color]<br><br>

    <i class="fa fa-link"></i> {{ trans('pages.link') }} http://site.com<br>
    <i class="fa fa-link"></i> {{ trans('pages.link_text') }}: [url=http://site.com]site.com[/url]<br>
    <i class="fa fa-link"></i> {{ trans('pages.link_short') }}: [url]http://site.com[/url]<br><br>

    <i class="fa fa-image"></i> [img]{{ trans('pages.image') }}[/img]<br>{!! bbCode('[img]/assets/img/images/logo.png[/img]') !!}<br>
    <i class="fab fa-youtube"></i> [youtube]{{ trans('pages.video') }}[/youtube]<br>{!! bbCode('[youtube]https://www.youtube.com/watch?v=yf_YWiqqv34[/youtube]') !!}<br>

    <i class="fa fa-align-center"></i> [center]{{ trans('pages.center') }}[/center]{!! bbCode('[center]' . trans('pages.center') . '[/center]') !!}<br>
    <i class="fa fa-list-ul"></i> [list]{{ trans('pages.unorderedlist') }}[/list]{!! bbCode('[list]' . trans('pages.unorderedlist') . '[/list]') !!}<br>
    <i class="fa fa-list-ol"></i> [list=1]{{ trans('pages.orderedlist') }}[/list]{!! bbCode('[list=1]' . trans('pages.orderedlist') . '[/list]') !!}<br>

    <i class="fa fa-text-height"></i> [spoiler]{{ trans('pages.spoiler_text') }}[/spoiler]{!! bbCode('[spoiler]' . trans('pages.spoiler_text') . '[/spoiler]') !!}<br>
    <i class="fa fa-text-height"></i> [spoiler={{ trans('pages.spoiler_title') }}]{{ trans('pages.spoiler_text') }}[/spoiler]{!! bbCode('[spoiler=' . trans('pages.spoiler_title') . ']' . trans('pages.spoiler_text') . '[/spoiler]') !!}<br>

    <i class="fa fa-eye-slash"></i> [hide]{{ trans('pages.hide_text') }}[/hide]{!! bbCode('[hide]' . trans('pages.hide_text') . '[/hide]') !!}<br>
    <i class="fa fa-quote-right"></i> [quote]{{ trans('pages.quote') }}[/quote]{!! bbCode('[quote]' . trans('pages.quote') . '[/quote]') !!}<br>
    <i class="fa fa-quote-right"></i> [quote={{ trans('pages.quote_author') }}]{{ trans('pages.quote') }}[/quote]{!! bbCode('[quote=' . trans('pages.quote_author')  . ']' . trans('pages.quote') . '[/quote]') !!}<br>

    <i class="fa fa-code"></i> [code]{{ trans('pages.code') }}[/code]{!! bbCode('[code]' . trans('pages.code') . '[/code]') !!}<br>
    <i class="fa fa-cut"></i> [cut] - {{ trans('pages.cutpage') }}<br>
    <i class="fa fa-eraser"></i> {{ trans('pages.clean_text') }}<br>
    <i class="fa fa-smile"></i> {{ trans('pages.sticker') }}<br>
    <i class="fa fa-check-square"></i> {{ trans('pages.preview') }}<br><br>
@stop
