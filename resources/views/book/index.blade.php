@extends('layout')

@section('title')
    {{ trans('book.title', ['page' => $page['current']]) }} - @parent
@stop

@section('content')

    <h1>{{ trans('book.header') }}</h1>

    <a href="/rules">Правила</a> /
    <a href="/smiles">Смайлы</a> /
    <a href="/tags">Теги</a>

    @if (isAdmin())
        / <a href="/admin/book?page={{ $page['current'] }}">Управление</a>
    @endif
    <hr>

    @if ($posts->isNotEmpty())
        @foreach ($posts as $data)

            <div class="post">
                <div class="b">

                    @if (getUser() && getUser('id') != $data->user_id)
                        <div class="float-right">
                            <a href="#" onclick="return postReply(this)" title="Ответить"><i class="fa fa-reply text-muted"></i></a>
                            <a href="#" onclick="return postQuote(this)" title="Цитировать"><i class="fa fa-quote-right text-muted"></i></a>

                            <a href="#" onclick="return sendComplaint(this)" data-type="{{ App\Models\Guest::class }}" data-id="{{ $data->id }}" data-token="{{ $_SESSION['token'] }}" data-page="{{ $page['current'] }}" rel="nofollow" title="Жалоба"><i class="fa fa-bell text-muted"></i></a>
                        </div>

                    @endif

                    @if (getUser() && getUser('id') == $data->user_id && $data->created_at + 600 > SITETIME)
                        <div class="float-right">
                            <a href="/book/edit/{{ $data->id }}" title="Редактировать"><i class="fa fa-pencil text-muted"></i></a>
                        </div>
                    @endif

                    <div class="img">{!! userAvatar($data->user) !!}</div>

                    @if (empty($data->user_id))
                        <b>{{ setting('guestsuser') }}</b> <small>({{ dateFixed($data->created_at) }})</small>
                    @else
                        <b>{!! profile($data->user) !!}</b> <small>({{ dateFixed($data->created_at) }})</small><br>
                        {!! userStatus($data->user) !!} {!! userOnline($data->user) !!}
                    @endif
                </div>

                <div class="message">{!! bbCode($data->text) !!}</div>

                @if ($data->edit_user_id)
                    <small><i class="fa fa-exclamation-circle text-danger"></i> Отредактировано: {{ $data->editUser->login }} ({{ dateFixed($data->updated_at) }})</small><br>
                @endif

                @if (isAdmin())
                    <span class="data">({{ $data->brow }}, {{ $data->ip }})</span>
                @endif

                @if ($data->reply))
                    <br><span style="color:#ff0000">Ответ: {!! bbCode($data->reply) !!}</span>
                @endif
            </div>
        @endforeach

        {{ pagination($page) }}

    @else
        {{ showError('Сообщений нет, будь первым!') }}
    @endif

    @if (getUser())
        <div class="form">
            <form action="book/add" method="post">
                <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">
                <div class="form-group{{ hasError('msg') }}">
                    <label for="markItUp">Сообщение:</label>
                    <textarea class="form-control" id="markItUp" rows="5" name="msg" placeholder="Текст сообщения" required>{{ getInput('msg') }}</textarea>
                    {!! textError('msg') !!}
                </div>

                <button class="btn btn-primary">Написать</button>
            </form>
        </div><br>

    @elseif (setting('bookadds') == 1)

        <div class="form">
            <form action="book/add" method="post">
                <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

                <div class="form-group{{ hasError('msg') }}">
                    <label for="inputText">Сообщение:</label>
                    <textarea class="form-control" id="inputText" rows="5" name="msg" placeholder="Текст сообщения" required>{{ getInput('msg') }}</textarea>
                    {!! textError('msg') !!}
                </div>

                <div class="form-group{{ hasError('protect') }}">
                    <label for="inputProtect">Проверочный код:</label>
                    <img src="/captcha" id="captcha" onclick="this.src='/captcha?'+Math.random()" class="rounded" alt="" style="cursor: pointer;">
                    <input class="form-control" name="protect" id="inputProtect" maxlength="6" required>
                    {!! textError('protect') !!}
                </div>

                <button class="btn btn-primary">Написать</button>
            </form>
        </div><br>

    @else
        {{ showError('Для добавления сообщения необходимо авторизоваться') }}
    @endif
@stop
