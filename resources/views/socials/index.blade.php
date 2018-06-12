@extends('layout')

@section('title')
    {{ trans('socials.title') }}
@stop

@section('content')

    <h1>{{ trans('socials.title') }}</h1>

    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/menu">{{ trans('common.menu') }}</a></li>
            <li class="breadcrumb-item active">{{ trans('socials.title') }}</li>
        </ol>
    </nav>

    {{ trans('socials.text_choise') }}

    <script src="//ulogin.ru/js/ulogin.js"></script>
    <div class="mb-3" id="uLogin" data-ulogin="display=panel;fields=first_name,last_name,photo;optional=bdate,sex,email,nickname;providers=vkontakte,odnoklassniki,mailru,facebook,twitter,google,yandex;redirect_uri={{ siteUrl() }}%2Fsocials">
    </div>

    @if ($socials->isNotEmpty())
        @foreach ($socials as $social)
            <div class="post">
                <div class="b">
                    <i class="fas fa-chevron-circle-right"></i> <b>{{ $social->network }}</b> ({{ trans('socials.added') }}: {{ dateFixed($social->created_at) }})

                    <div class="float-right">
                        <a href="/socials/delete/{{ $social->id }}?token={{ $_SESSION['token'] }}" onclick="return confirm('{{ trans('socials.text_confirm') }}')"><i class="fas fa-times"></i></a>
                    </div>
                </div>
            </div>
        @endforeach

        <br>{{ trans('socials.total') }}: <b>{{ $socials->count() }}</b><br>

    @else
        {!! showError(trans('socials.empty_records')) !!}
    @endif

@stop
