@extends('layout')

@section('title', __('index.site_settings'))

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ __('index.panel') }}</a></li>
            <li class="breadcrumb-item active">{{ __('index.site_settings') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-4 section shadow">
                <div class="nav flex-column nav-pills">
                    <a class="nav-link" href="/admin/settings?act=mains" id="mains">{{ __('settings.mains') }}</a>
                    <a class="nav-link" href="/admin/settings?act=mails" id="mails">{{ __('settings.mails') }}</a>
                    <a class="nav-link" href="/admin/settings?act=info" id="info">{{ __('settings.info') }}</a>
                    <a class="nav-link" href="/admin/settings?act=guestbook" id="guestbook">{{ __('settings.guestbook') }}</a>
                    <a class="nav-link" href="/admin/settings?act=news" id="news">{{ __('settings.news') }}</a>
                    <a class="nav-link" href="/admin/settings?act=comments" id="comments">{{ __('settings.comments') }}</a>
                    <a class="nav-link" href="/admin/settings?act=forums" id="forums">{{ __('settings.forums') }}</a>
                    <a class="nav-link" href="/admin/settings?act=photos" id="photos">{{ __('settings.photos') }}</a>
                    <a class="nav-link" href="/admin/settings?act=messages" id="messages">{{ __('settings.messages') }}</a>
                    <a class="nav-link" href="/admin/settings?act=contacts" id="contacts">{{ __('settings.contacts') }}</a>
                    <a class="nav-link" href="/admin/settings?act=loads" id="loads">{{ __('settings.loads') }}</a>
                    <a class="nav-link" href="/admin/settings?act=blogs" id="blogs">{{ __('settings.blogs') }}</a>
                    <a class="nav-link" href="/admin/settings?act=pages" id="pages">{{ __('settings.pages') }}</a>
                    <a class="nav-link" href="/admin/settings?act=others" id="others">{{ __('settings.others') }}</a>
                    <a class="nav-link" href="/admin/settings?act=protects" id="protects">{{ __('settings.protects') }}</a>
                    <a class="nav-link" href="/admin/settings?act=prices" id="prices">{{ __('settings.prices') }}</a>
                    <a class="nav-link" href="/admin/settings?act=adverts" id="adverts">{{ __('settings.adverts') }}</a>
                    <a class="nav-link" href="/admin/settings?act=images" id="images">{{ __('settings.images') }}</a>
                    <a class="nav-link" href="/admin/settings?act=stickers" id="stickers">{{ __('settings.stickers') }}</a>
                    <a class="nav-link" href="/admin/settings?act=offers" id="offers">{{ __('settings.offers') }}</a>
                </div>
            </div>
            <div class="col-md-8 section shadow">
                @include('admin/settings/_' . $act)
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
