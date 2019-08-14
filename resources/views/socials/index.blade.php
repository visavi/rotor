@extends('layout')

@section('title')
    {{ trans('index.social_networks') }}
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/menu">{{ trans('main.menu') }}</a></li>
            <li class="breadcrumb-item active">{{ trans('index.social_networks') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    {{ trans('socials.text_choice') }}

    <script src="//ulogin.ru/js/ulogin.js" async defer></script>
    <div class="mb-3" id="uLogin" data-ulogin="display=panel;providers=vkontakte,odnoklassniki,mailru,facebook,google,yandex,instagram;redirect_uri={{ siteUrl() }}/socials;mobilebuttons=0;">
    </div>

    @if ($socials->isNotEmpty())
        @foreach ($socials as $social)
            <div class="post">
                <div class="b">
                    <i class="fas fa-chevron-circle-right"></i> <b>{{ $social->network }}</b> ({{ trans('main.added') }}: {{ dateFixed($social->created_at) }})

                    <div class="float-right">
                        <a href="/socials/delete/{{ $social->id }}?token={{ $_SESSION['token'] }}" onclick="return confirm('{{ trans('socials.text_confirm') }}')"><i class="fas fa-times"></i></a>
                    </div>
                </div>
            </div>
        @endforeach

        <br>{{ trans('main.total') }}: <b>{{ $socials->count() }}</b><br>

    @else
        {!! showError(trans('socials.empty_records')) !!}
    @endif
@stop
