@extends('layout')

@section('title')
    Приватные сообщения
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/menu">Мое меню</a></li>
            <li class="breadcrumb-item">Сообщения</li>
        </ol>
    </nav>
@stop

@section('content')
    @if ($messages->isNotEmpty())
        @foreach ($messages as $data)
            <?php $link = $data->author->id ? '/' . $data->author->login : ''; ?>
            <div class="media border-bottom p-2" data-href="/messages/talk{{ $link }}">
                <div class="img mr-3">
                    {!! $data->author->getAvatar() !!}
                    {!! $data->author->getOnline() !!}
                </div>
                <div class="media-body">
                    <div class="text-muted float-right">
                        {{  dateFixed($data->created_at) }}
                        <a href="/messages/delete/{{ (int) $data->author->id }}?token={{ $_SESSION['token'] }}&amp;page={{ $page->current }}" onclick="return confirm('Вы действительно хотите удалить сообщения?')" data-toggle="tooltip" title="Удалить"><i class="fa fa-times"></i></a>
                    </div>

                    @if ($data->author->id)
                        <b>{!! $data->author->getProfile() !!}</b>
                    @else
                        <b>Система</b><br>
                    @endif

                    <div class="message">
                        {{ $data->type === 'out' ? 'Вы: ' : '' }}
                        {!! truncateWord(bbCode($data->text)) !!}
                    </div>
                    @unless ($data->reading)
                        <span class="badge badge-info">Новое</span>
                    @endunless
                </div>
            </div>
        @endforeach

        {!! pagination($page) !!}
    @else
        {!! showError('Сообщений еще нет!') !!}
    @endif

    <i class="fa fa-search"></i> <a href="/searchusers">Поиск пользователей</a><br>
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
