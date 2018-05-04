@extends('layout')

@section('title')
    Полученные голоса {{ $user->login }}
@stop

@section('content')

    <h1>Полученные голоса {{ $user->login }}</h1>

    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/users/{{ $user->login }}">{{ $user->login }}</a></li>

            @if (getUser('id') != $user->id)
                <li class="breadcrumb-item"><a href="/users/{{ $user->login }}/rating">Изменения репутации</a></li>
            @endif

            <li class="breadcrumb-item active">Полученные голоса</li>
        </ol>
    </nav>

    <i class="fa fa-thumbs-up"></i> <b>Полученные</b> / <a href="/ratings/{{ $user->login }}/gave">Отданные</a><hr>

    @if ($ratings->isNotEmpty())
        @foreach ($ratings as $data)
            <div class="post">
                <div class="b">
                    @if (empty($data['vote']))
                        <i class="fa fa-thumbs-down text-danger"></i>
                    @else
                        <i class="fa fa-thumbs-up text-success"></i>
                    @endif

                    <b>{!! profile($data->user) !!}</b> ({{ dateFixed($data->created_at) }})

                    <div class="float-right">
                        @if (isAdmin())
                            <a href="#" onclick="return deleteRating(this)" data-id="{{ $data->id }}" data-token="{{ $_SESSION['token'] }}" data-toggle="tooltip" title="Удалить"><i class="fa fa-times"></i></a>
                        @endif
                    </div>
                </div>
                <div>
                    Комментарий:

                    @if ($data->text)
                        {!! bbCode($data->text) !!}
                    @else
                        Отсутствует
                    @endif
                </div>
            </div>
        @endforeach

        {!! pagination($page) !!}
    @else
        {!! showError('В истории еще ничего нет!') !!}
    @endif
@stop
