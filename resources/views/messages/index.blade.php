@extends('layout')

@section('title')
    {{ __('index.messages') }}
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/menu">{{ __('main.menu') }}</a></li>
            <li class="breadcrumb-item">{{ __('index.messages') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    @if ($messages->isNotEmpty())
        @foreach ($messages as $data)
            <div class="media border-bottom p-2" data-href="/messages/talk/{{ $data->author_id }}">
                <div class="img mr-3">
                    @if($data->author_id === 0)
                        <img class="avatar" src="/assets/img/images/avatar_system.png" alt="">
                        <div class="online bg-success" title="Online"></div>
                    @else
                        {!! $data->author->getAvatar() !!}
                        {!! $data->author->getOnline() !!}
                    @endif
                </div>
                <div class="media-body">
                    <div class="text-muted float-right">
                        {{  dateFixed($data->created_at) }}
                        <a href="/messages/delete/{{ $data->author_id }}?token={{ $_SESSION['token'] }}&amp;page={{ $page->current }}" onclick="return confirm('{{ __('messages.delete_confirm') }}')" data-toggle="tooltip" title="{{ __('main.delete') }}"><i class="fa fa-times"></i></a>
                    </div>

                    @if($data->author_id === 0)
                        <b>{{ __('messages.system') }}</b>
                    @else
                        <b>{!! $data->author->getProfile() !!}</b>
                    @endif

                    <div class="message">
                        {{ $data->type === 'out' ? __('messages.you') . ': ' : '' }}
                        {!! bbCodeTruncate($data->text) !!}
                    </div>
                    @unless ($data->reading)
                        <span class="badge badge-info">{{ __('messages.new') }}</span>
                    @endunless
                </div>
            </div>
        @endforeach

        {!! pagination($page) !!}
    @else
        {!! showError(__('main.empty_messages')) !!}
    @endif

    <i class="fa fa-search"></i> <a href="/searchusers">{{ __('index.user_search') }}</a><br>
    <i class="fa fa-address-book"></i> <a href="/contacts">{{ __('index.contacts') }}</a> / <a href="/ignores">{{ __('index.ignores') }}</a><br>
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
