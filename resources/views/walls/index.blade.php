@extends('layout')

@section('title', __('index.wall_posts_login', ['login' => $user->getName()]))

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/users/{{ $user->login }}">{{ $user->getName() }}</a></li>
            <li class="breadcrumb-item active">{{ __('index.wall_posts') }}</li>
        </ol>
    </nav>
@endsection

@section('content')
    @if ($newWall)
        <div class="fw-bold text-danger text-center my-3">
            {{ __('walls.new') }}: {{ $newWall }}
        </div>
    @endif

    @if ($messages->isNotEmpty())
        @foreach ($messages as $data)
            <div class="section mb-3 shadow">
                <div class="user-avatar">
                    {{ $data->author->getAvatar() }}
                    {{ $data->author->getOnline() }}
                </div>

                <div class="section-user d-flex align-items-start">
                    <div class="flex-grow-1">
                        {{ $data->author->getProfile() }}
                        <small class="section-date text-muted fst-italic">{{ dateFixed($data->created_at) }}</small><br>
                        <small class="fst-italic">{{ $data->author->getStatus() }}</small>
                    </div>

                    @if (getUser())
                        <div class="text-end section-action">
                            @if (getUser('id') !== $data->author_id)
                                <a href="#" onclick="return postReply(this)" title="{{ __('main.reply') }}"><i class="fa fa-reply text-muted"></i></a>
                                <a href="#" onclick="return postQuote(this)" title="{{ __('main.quote') }}"><i class="fa fa-quote-right text-muted"></i></a>

                                <a href="#" onclick="return sendComplaint(this)" data-type="{{ $data->getMorphClass() }}" data-id="{{ $data->id }}" data-token="{{ csrf_token() }}" data-page="{{ $messages->currentPage() }}" rel="nofollow" title="{{ __('main.complain') }}"><i class="fa fa-bell text-muted"></i></a>
                            @endif

                            @if (isAdmin() || getUser('id') === $user->id)
                                <a href="#" onclick="return deleteWall(this)" data-id="{{ $data->id }}" data-login="{{ $data->user->login }}" data-token="{{ csrf_token() }}" data-bs-toggle="tooltip" title="{{ __('main.delete') }}"><i class="fa fa-times text-muted"></i></a>
                            @endif
                        </div>
                    @endif
                </div>

                <div class="section-body border-top">
                    <div class="section-message">
                        {{ bbCode($data->text) }}
                    </div>
                </div>
            </div>
        @endforeach

        {{ $messages->links() }}

        <div class="mb-3">
            {{ __('main.total') }}: <b>{{ $messages->total() }}</b>
        </div>
    @else
        {{ showError(__('walls.empty_messages')) }}
    @endif

    @if (getUser())
        <div class="section-form mb-3 shadow">
            <form action="/walls/{{ $user->login }}/create" method="post">
                @csrf
                <div class="mb-3{{ hasError('msg') }}">
                    <label for="msg" class="form-label">{{ __('main.message') }}:</label>
                    <textarea class="form-control markItUp" id="msg" rows="5" name="msg" placeholder="{{ __('main.message') }}" required>{{ getInput('msg') }}</textarea>
                    <div class="invalid-feedback">{{ textError('msg') }}</div>
                </div>

                <button class="btn btn-primary">{{ __('main.write') }}</button>
            </form>
        </div>

    @else
        {{ showError(__('main.not_authorized')) }}
    @endif
@stop
