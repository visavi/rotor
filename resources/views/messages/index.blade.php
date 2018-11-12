@extends('layout')

@section('title')
    Приватные сообщения
@stop

@section('content')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/menu">Мое меню</a></li>
            <li class="breadcrumb-item">Сообщения</li>
        </ol>
    </nav>

    <h1>Приватные сообщения</h1>

    @if ($messages->isNotEmpty())
        @foreach ($messages as $data)
            <div class="media border-bottom p-2" data-href="/messages/talk/{{ $data->talkUser->login }}">
                <div class="img mr-3">
                    {!! $data->talkUser->getAvatar() !!}
                    {!! $data->talkUser->getOnline() !!}
                </div>
                <div class="media-body">
                    <div class="text-muted float-right">
                        {{  dateFixed($data->created_at) }}
                    </div>

                    @if ($data->talkUser->id)
                        <b>{!! $data->talkUser->getProfile() !!}</b>
                    @else
                        <b>Система</b><br>
                    @endif

                    <div class="message">{!! stripString(bbCode($data->text)) !!}</div>
                    @unless ($data->read)
                        <span class="badge badge-info">Новое</span>
                    @endunless
                </div>
            </div>
        @endforeach

        {!! pagination($page) !!}

        <i class="fa fa-times"></i> <a href="/messages/clear?token={{ $_SESSION['token'] }}">Очистить ящик</a><br>
    @else
        {!! showError('Сообщений еще нет!') !!}
    @endif

    <i class="fa fa-search"></i> <a href="/searchusers">Поиск пользователей</a><br>
    <i class="fa fa-envelope"></i> <a href="/messages/send">Написать письмо</a><br>
    <i class="fa fa-address-book"></i> <a href="/contacts">Контакт</a> / <a href="/ignores">Игнор</a><br>
@stop

@push('styles')
    <style>
        .media {
            cursor: pointer;
        }

        .media:hover {
            background-color: #e9ecef;
        }
    </style>
@endpush

@push('scripts')
    <script>
        $(document).ready(function() {
            $('.media').on('click', function() {
                window.location = $(this).data('href');
                return false;
            }).find('a').on('click', function (e) {
                e.stopPropagation();
            });
        });
    </script>
@endpush
