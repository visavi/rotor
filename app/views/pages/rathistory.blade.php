@extends('layout')

@section('title')
    Полученные голоса {{ $user->login }} - @parent
@stop

@section('content')

    <h1>Полученные голоса {{ $user->login }}</h1>

    <i class="fa fa-thumbs-up"></i> <b>Полученные</b> / <a href="/rating/{{ $user->login }}/gave">Отданные</a><hr />

    @if ($ratings)
        @foreach ($ratings as $data)
            <div class="post">
                <div class="b">
                    @if (empty($data['vote']))
                        <i class="fa fa-thumbs-down text-danger"></i>
                    @else
                        <i class="fa fa-thumbs-up text-success"></i>
                    @endif

                    <b>{!! profile($data->user) !!}</b> ({{ date_fixed($data['created_at']) }})

                    <div class="pull-right">
                        @if (is_admin())
                            <a href="#" onclick="return deleteRating(this)" data-id="{{ $data['id'] }}" data-login="{{ $data->getUser()->login }}" data-token="{{ $_SESSION['token'] }}" data-toggle="tooltip" title="Удалить"><i class="fa fa-remove"></i></a>
                        @endif
                    </div>
                </div>
                <div>
                    Комментарий:

                    @if ($data['text'])
                        {!! App::bbCode($data['text']) !!}
                    @else
                        Отсутствует
                    @endif
                </div>
            </div>
        @endforeach
    @else
        {{  show_error('В истории еще ничего нет!') }}
    @endif

    <br />
    <i class="fa fa-arrow-circle-up"></i> <a href="/user/{{  $user->login }}">В анкету</a><br />
@stop
