@extends('layout')

@section('title')
    Отданные голоса {{ $user->login }}
@stop

@section('content')

    <h1>Отданные голоса {{ $user->login }}</h1>

    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/users/{{ $user->login }}">{{ $user->login }}</a></li>

            @if (getUser('id') !== $user->id)
                <li class="breadcrumb-item"><a href="/users/{{ $user->login }}/rating">Изменения репутации</a></li>
            @endif

            <li class="breadcrumb-item active">Отданные голоса</li>
        </ol>
    </nav>

    <i class="fa fa-thumbs-up"></i> <a href="/ratings/{{ $user->login }}/received">Полученные</a> / <b>Отданные</b><hr>

    @if ($ratings->isNotEmpty())
        @foreach ($ratings as $data)
            <div class="b">
                @if ($data->vote === '-')
                    <i class="fa fa-thumbs-down text-danger"></i>
                @else
                    <i class="fa fa-thumbs-up text-success"></i>
                @endif

                <b>{!! $data->recipient->getProfile() !!}</b> ({{ dateFixed($data->created_at) }})
            </div>
            <div>
                Комментарий:

                @if ($data['text'])
                    {!! bbCode($data->text) !!}
                @else
                    Отсутствует
                @endif
            </div>
        @endforeach

        {!! pagination($page) !!}
    @else
        {!! showError('В истории еще ничего нет!') !!}
    @endif
@stop
