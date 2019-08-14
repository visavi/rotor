@extends('layout')

@section('title')
    {{ trans('index.guestbooks') }}
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ trans('index.panel') }}</a></li>
            <li class="breadcrumb-item active">{{ trans('index.guestbooks') }}</li>
            <li class="breadcrumb-item"><a href="/guestbooks?page={{ $page->current }}">{{ trans('main.review') }}</a></li>
        </ol>
    </nav>
@stop

@section('content')
    @if ($posts->isNotEmpty())
        <form action="/admin/guestbooks/delete?page={{ $page->current }}" method="post">
            @csrf
            @foreach($posts as $data)
                <div class="post">
                    <div class="b">
                        <div class="img">
                            {!! $data->user->getAvatar() !!}

                            @if ($data->user_id)
                                {!! $data->user->getOnline() !!}
                            @endif
                        </div>

                        <div class="float-right">
                            <a href="/admin/guestbooks/reply/{{ $data->id }}?page={{ $page->current }}"><i class="fa fa-reply text-muted"></i></a>
                            <a href="/admin/guestbooks/edit/{{ $data->id }}?page={{ $page->current }}"><i class="fas fa-pencil-alt text-muted"></i></a>
                            <input type="checkbox" name="del[]" value="{{ $data->id }}">
                        </div>

                        @if ($data->user_id)
                            <b>{!! $data->user->getProfile() !!}</b> <small>({{ dateFixed($data->created_at) }})</small><br>
                            {!! $data->user->getStatus() !!}
                        @else
                            <b>{{ setting('guestsuser') }}</b> <small>({{ dateFixed($data->created_at) }})</small>
                        @endif
                    </div>

                    <div class="message">{!! bbCode($data->text) !!}</div>

                    @if ($data->edit_user_id)
                        <small><i class="fa fa-exclamation-circle text-danger"></i> {{ trans('main.changed') }}: {{ $data->editUser->getName() }} ({{ dateFixed($data->updated_at) }})</small><br>
                    @endif

                    <span class="data">({{ $data->brow }}, {{ $data->ip }})</span>

                    @if ($data->reply)
                        <br><span style="color:#ff0000">{{ trans('main.reply') }}: {!! bbCode($data->reply) !!}</span>
                    @endif
                </div>
            @endforeach

            <div class="float-right">
                <button class="btn btn-sm btn-danger">{{ trans('main.delete_selected') }}</button>
            </div>
        </form>

        {!! pagination($page) !!}

        {{ trans('guestbooks.total_messages') }}: <b>{{ $page->total }}</b><br><br>

        @if (isAdmin('boss'))
            <i class="fa fa-times"></i> <a href="/admin/guestbooks/clear?token={{ $_SESSION['token'] }}" onclick="return confirm('{{ trans('guestbooks.confirm_delete') }}')">{{ trans('main.clear') }}</a><br>
        @endif
    @else
        {!! showError(trans('main.empty_messages')) !!}
    @endif
@stop
