@extends('layout')

@section('title')
    {{ trans('index.site_settings') }}
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ trans('index.panel') }}</a></li>
            <li class="breadcrumb-item active">{{ trans('index.site_settings') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-4 bg-light p-1">
                <div class="nav flex-column nav-pills">
                    <a class="nav-link" href="/admin/settings?act=mains" id="mains">{{ trans('settings.mains') }}</a>
                    <a class="nav-link" href="/admin/settings?act=mails" id="mails">{{ trans('settings.mails') }}</a>
                    <a class="nav-link" href="/admin/settings?act=info" id="info">{{ trans('settings.info') }}</a>
                    <a class="nav-link" href="/admin/settings?act=guestbooks" id="guestbooks">{{ trans('settings.guestbooks') }}</a>
                    <a class="nav-link" href="/admin/settings?act=news" id="news">{{ trans('settings.news') }}</a>
                    <a class="nav-link" href="/admin/settings?act=comments" id="comments">{{ trans('settings.comments') }}</a>
                    <a class="nav-link" href="/admin/settings?act=forums" id="forums">{{ trans('settings.forums') }}</a>
                    <a class="nav-link" href="/admin/settings?act=photos" id="photos">{{ trans('settings.photos') }}</a>
                    <a class="nav-link" href="/admin/settings?act=messages" id="messages">{{ trans('settings.messages') }}</a>
                    <a class="nav-link" href="/admin/settings?act=contacts" id="contacts">{{ trans('settings.contacts') }}</a>
                    <a class="nav-link" href="/admin/settings?act=loads" id="loads">{{ trans('settings.loads') }}</a>
                    <a class="nav-link" href="/admin/settings?act=blogs" id="blogs">{{ trans('settings.blogs') }}</a>
                    <a class="nav-link" href="/admin/settings?act=pages" id="pages">{{ trans('settings.pages') }}</a>
                    <a class="nav-link" href="/admin/settings?act=others" id="others">{{ trans('settings.others') }}</a>
                    <a class="nav-link" href="/admin/settings?act=protects" id="protects">{{ trans('settings.protects') }}</a>
                    <a class="nav-link" href="/admin/settings?act=prices" id="prices">{{ trans('settings.prices') }}</a>
                    <a class="nav-link" href="/admin/settings?act=adverts" id="adverts">{{ trans('settings.adverts') }}</a>
                    <a class="nav-link" href="/admin/settings?act=images" id="images">{{ trans('settings.images') }}</a>
                    <a class="nav-link" href="/admin/settings?act=stickers" id="stickers">{{ trans('settings.stickers') }}</a>
                    <a class="nav-link" href="/admin/settings?act=offers" id="offers">{{ trans('settings.offers') }}</a>
                </div>
            </div>
            <div class="col-md-8">
                @include ('admin/settings/_' . $act)
            </div>
        </div>
    </div>
@stop

@push('scripts')
    <script>
        $(function () {
            $('#{{ $act }}').addClass('active');
        })
    </script>
@endpush
