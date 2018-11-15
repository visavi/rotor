@extends('layout')

@section('title')
    Диалог с {!! $user->getProfile(null, false) !!}
@stop

@section('content')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/menu">Мое меню</a></li>
            <li class="breadcrumb-item"><a href="/messages">Приватные сообщения</a></li>
            <li class="breadcrumb-item active">Диалог</li>
        </ol>
    </nav>

    <h1>Диалог с {{ $user->getProfile(null, false) }}</h1>

    @if (getUser()->isIgnore($user))
        <div class="p-1 bg-danger text-white">
            <i class="fas fa-exclamation-triangle"></i>
            Внимание, данный пользователь находится в игнор-листе!
        </div>
    @endif

    @if ($messages->isNotEmpty())

        @foreach ($messages as $data)

            <?php $author = $data->type === 'in' ? $data->author : $data->user; ?>
            <div class="post">
                <div class="b">
                    <div class="img">
                        {!! $author->getAvatar() !!}
                        {!! $author->getOnline() !!}
                    </div>

                    <div class="text-muted float-right">
                        {{  dateFixed($data->created_at) }}

                        @if ($data->type === 'in')
                            <a href="#" onclick="return sendComplaint(this)" data-type="{{ App\Models\Message::class }} " data-id="{{ $data->id }}" data-token="{{ $_SESSION['token'] }}" rel="nofollow" title="Жалоба"><i class="fa fa-bell text-muted"></i></a>
                        @endif
                    </div>

                    <b>{!! $author->getProfile() !!}</b>

                    @unless ($data->read)
                        <br><span class="badge badge-info">Новое</span>
                    @endunless
                </div>
                <div class="message">{!! bbCode($data->text) !!}</div>
            </div>
        @endforeach

        {!! pagination($page) !!}
    @else
        {!! showError('История переписки отсутствует!') !!}
    @endif

    <br>
    <div class="form">
        <form action="/messages/send?user={{ $user->login }}" method="post">
            <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

            <div class="form-group{{ hasError('msg') }}">
                <label for="msg">Сообщение:</label>
                <textarea class="form-control markItUp" id="msg" rows="5" name="msg" placeholder="Текст сообщения" required>{{ getInput('msg') }}</textarea>
                {!! textError('msg') !!}
            </div>

            @if (getUser('point') < setting('privatprotect'))
                {!! view('app/_captcha') !!}
            @endif

            <button class="btn btn-primary">Быстрый ответ</button>
        </form>
    </div><br>

    Писем: <b>{{ $page->total }}</b><br><br>

    @if ($page->total)
        <i class="fa fa-times"></i> <a href="/messages/delete/{{ $user->id }}?token={{ $_SESSION['token'] }}">Удалить переписку</a><br>
    @endif

    <i class="fa fa-search"></i> <a href="/searchusers">Поиск пользователей</a><br>
    <i class="fa fa-envelope"></i> <a href="/messages/send">Написать письмо</a><br>
    <i class="fa fa-address-book"></i> <a href="/contacts">Контакт</a> / <a href="/ignores">Игнор</a><br>
@stop
