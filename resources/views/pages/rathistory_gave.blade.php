@extends('layout')

@section('title')
    Отданные голоса {{ $user->login }} - @parent
@stop

@section('content')

    <h1>Отданные голоса {{ $user->login }}</h1>

    <i class="fa fa-thumbs-up"></i> <a href="/rating/{{ $user->login }}/received">Полученные</a> / <b>Отданные</b><hr>

    @if ($ratings->isNotEmpty())
        @foreach ($ratings as $data)
            <div class="b">
                @if (empty($data['vote']))
                    <i class="fa fa-thumbs-down text-danger"></i>
                @else
                    <i class="fa fa-thumbs-up text-success"></i>
                @endif

                <b>{!! profile($data->recipient) !!}</b> ({{ dateFixed($data['created_at']) }})
            </div>
            <div>
                Комментарий:

                @if ($data['text'])
                    {!! bbCode($data['text']) !!}
                @else
                    Отсутствует
                @endif
            </div>
        @endforeach
    @else
        {{  showError('В истории еще ничего нет!') }}
    @endif

    <br>
    <i class="fa fa-arrow-circle-up"></i> <a href="/user/{{  $user->login }}">В анкету</a><br>
@stop
