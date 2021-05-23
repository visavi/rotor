@extends('layout')

@section('title', __('index.social_networks'))

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/menu">{{ __('main.menu') }}</a></li>
            <li class="breadcrumb-item active">{{ __('index.social_networks') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    {{ __('socials.text_choice') }}

    <script src="//ulogin.ru/js/ulogin.js" async defer></script>
    <div class="mb-3" id="uLogin" data-ulogin="display=panel;providers=vkontakte,odnoklassniki,mailru,facebook,google,yandex,instagram;redirect_uri={{ siteUrl() }}/socials;mobilebuttons=0;">
    </div>

    @if ($socials->isNotEmpty())
        @foreach ($socials as $social)
            <div class="section-form mb-3 shadow">
                <i class="fas fa-chevron-circle-right"></i> <b>{{ $social->network }}</b> ({{ __('main.added') }}: {{ dateFixed($social->created_at) }})

                <div class="float-end">
                    <a href="/socials/delete/{{ $social->id }}?token={{ csrf_token() }}" onclick="return confirm('{{ __('socials.text_confirm') }}')"><i class="fas fa-times"></i></a>
                </div>
            </div>
        @endforeach

        {{ __('main.total') }}: <b>{{ $socials->count() }}</b><br>

    @else
        {{ showError(__('socials.empty_records')) }}
    @endif
@stop
