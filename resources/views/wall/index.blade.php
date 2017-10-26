@extends('layout')

@section('title')
    Стена пользователя {{ $user->login }}
@stop

@section('content')

    <h1>Стена пользователя {{ $user->login }}</h1>

    @if ($newWall)
        <div style="text-align:center"><b><span style="color:#ff0000">Новых записей: {{ $newWall }}</span></b></div>
    @endif

    @if ($messages->isNotEmpty())
        @foreach ($messages as $data)
            <div class="post">
                <div class="b">

                    @if (isAdmin() || $user->id == getUser('id'))
                        <div class="float-right">
                            <a href="#" onclick="return deleteWall(this)" data-id="{{ $data->id }}" data-login="{{ $data->user->login }}" data-token="{{ $_SESSION['token'] }}" data-toggle="tooltip" title="Удалить"><i class="fa fa-remove text-muted"></i></a>
                        </div>
                    @endif

                    @if (getUser() && getUser('id') != $data->author_id)
                        <div class="float-right">
                            <a href="#" onclick="return postReply(this)" title="Ответить"><i class="fa fa-reply text-muted"></i></a>
                            <a href="#" onclick="return postQuote(this)" title="Цитировать"><i class="fa fa-quote-right text-muted"></i></a>

                            <a href="#" onclick="return sendComplaint(this)" data-type="{{ App\Models\Wall::class }}" data-id="{{ $data->id }}" data-token="{{ $_SESSION['token'] }}" data-page="{{ $page['current'] }}" rel="nofollow" title="Жалоба"><i class="fa fa-bell text-muted"></i></a>
                        </div>
                    @endif

                    <div class="img">{!! userAvatar($data->author) !!}</div>

                    <b>{!! profile($data->author) !!}</b> <small>({{ dateFixed($data->created_at) }})</small><br>
                    {!! userStatus($data->author) !!} {!! userOnline($data->author) !!}
                </div>
                <div class="message">{!! bbCode($data->text) !!}</div>
            </div>
        @endforeach

        {{ pagination($page) }}
    @else
        {{ showError('Записок еще нет!') }}
    @endif

    @if (getUser())

        <div class="form">
            <form action="/wall/{{ $user->login }}/create" method="post">
                <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

                <div class="form-group{{ hasError('msg') }}">
                    <label for="markItUp">Сообщение:</label>
                    <textarea class="form-control" id="markItUp" rows="5" name="msg" required>{{ getInput('msg') }}</textarea>
                    {!! textError('msg') !!}
                </div>

                <button class="btn btn-primary">Написать</button>
            </form>
        </div><br>

    @else
        {{ showError('Для добавления сообщения необходимо авторизоваться') }}
    @endif

    Всего записей: <b>{{ $page['total'] }}</b><br><br>
@stop
