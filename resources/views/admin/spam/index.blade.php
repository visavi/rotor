@extends('layout')

@section('title', __('index.complains'))

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ __('index.panel') }}</a></li>
            <li class="breadcrumb-item active">{{ __('index.complains') }}</li>
        </ol>
    </nav>
@stop

@section('content')

    <div class="mb-3">
        @foreach ($types as $key => $value)
            <a class="badge badge-{{ $key === $type ? 'success' : 'light' }}" href="/admin/spam?type={{ $key }}">{{ $value }} {{ $total[$key] }}</a>
        @endforeach
    </div>

    @if ($records->isNotEmpty())
        @foreach ($records as $record)
            <div class="section mb-3 shadow">
                <?php $user = $record->getRelateUser(); ?>
                <div class="user-avatar">
                    @if ($user)
                        {!! $user->getAvatar() !!}
                        {!! $user->getOnline() !!}
                    @else
                        <img class="avatar-default rounded-circle" src="/assets/img/images/avatar_guest.png" alt="">
                    @endif
                </div>
                <div class="section-user d-flex align-items-center">
                    <div class="flex-grow-1">
                        @if ($record->relate)
                            @if ($user)
                                <b>{!! $user->getProfile() !!}</b>
                            @else
                                <b>{{ setting('guestsuser') }}</b>
                            @endif

                            <small class="section-date text-muted font-italic">
                                {{ dateFixed($record->relate->created_at, 'd.m.Y / H:i:s') }}
                            </small>
                        @else
                            <b>{{ __('main.message_not_found') }}</b>
                        @endif
                    </div>

                    <div class="text-right">
                        @if (isAdmin())
                            <a href="#" onclick="return deleteSpam(this)" data-id="{{ $record->id }}" data-token="{{ $_SESSION['token'] }}" data-toggle="tooltip" title="{{ __('main.delete') }}"><i class="fa fa-times"></i></a>
                        @endif
                    </div>
                </div>

                <div class="section-body border-top my-1 py-1">
                    @if ($record->relate)
                        <div class="section-message">
                            {!! bbCode($record->relate->text) !!}
                        </div>

                        @if ($record->path)
                            <div class="mt-2">
                                <a href="{{ $record->path }}">{{ __('admin.spam.go_to_message') }}</a>
                            </div>
                        @endif
                    @endif

                    <div>
                        {{ __('main.sent') }}: {!! $record->user->getProfile() !!}
                        <small class="section-date text-muted font-italic">
                            {{ dateFixed($record->created_at) }}
                        </small>
                    </div>
                </div>
            </div>
        @endforeach
    @else
        {!! showError(__('admin.spam.empty_spam')) !!}
    @endif

    {{ $records->links() }}
@stop
