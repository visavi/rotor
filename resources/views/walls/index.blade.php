@extends('layout')

@section('title')
    {{ __('index.wall_posts') }} {{ $user->login }}
@stop

@section('content')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/users/{{ $user->login }}">{{ $user->login }}</a></li>
            <li class="breadcrumb-item active">{{ __('index.wall_posts') }}</li>
        </ol>
    </nav>

    @if ($newWall)
        <div style="text-align:center"><b><span style="color:#ff0000">{{ __('walls.new') }}: {{ $newWall }}</span></b></div>
    @endif

    @if ($messages->isNotEmpty())
        @foreach ($messages as $data)
            <div class="post">
                <div class="b">
                    <div class="float-right">
                        @if (getUser() && getUser('id') !== $data->author_id)
                            <a href="#" onclick="return postReply(this)" title="{{ __('main.reply') }}"><i class="fa fa-reply text-muted"></i></a>
                            <a href="#" onclick="return postQuote(this)" title="{{ __('main.quote') }}"><i class="fa fa-quote-right text-muted"></i></a>

                            <a href="#" onclick="return sendComplaint(this)" data-type="{{ App\Models\Wall::class }}" data-id="{{ $data->id }}" data-token="{{ $_SESSION['token'] }}" data-page="{{ $messages->currentPage() }}" rel="nofollow" title="{{ __('main.complain') }}"><i class="fa fa-bell text-muted"></i></a>
                        @endif

                        @if (isAdmin() || $user->id === getUser('id'))
                            <a href="#" onclick="return deleteWall(this)" data-id="{{ $data->id }}" data-login="{{ $data->user->login }}" data-token="{{ $_SESSION['token'] }}" data-toggle="tooltip" title="{{ __('main.delete') }}"><i class="fa fa-times text-muted"></i></a>
                        @endif
                    </div>

                    <div class="img">
                        {!! $data->author->getAvatar() !!}
                        {!! $data->author->getOnline() !!}
                    </div>

                    <b>{!! $data->author->getProfile() !!}</b> <small>({{ dateFixed($data->created_at) }})</small><br>
                    {!! $data->author->getStatus() !!}
                </div>
                <div class="section-message">
                    {!! bbCode($data->text) !!}
                </div>
            </div>
        @endforeach

        <br>{{ __('main.total') }}: <b>{{ $messages->total() }}</b><br>
    @else
        {!! showError(__('walls.empty_messages')) !!}
    @endif

    {{ $messages->links() }}

    @if (getUser())
        <div class="section-form p-2 shadow">
            <form action="/walls/{{ $user->login }}/create" method="post">
                @csrf
                <div class="form-group{{ hasError('msg') }}">
                    <label for="msg">{{ __('main.message') }}:</label>
                    <textarea class="form-control markItUp" id="msg" rows="5" name="msg" placeholder="{{ __('main.message') }}" required>{{ getInput('msg') }}</textarea>
                    <div class="invalid-feedback">{{ textError('msg') }}</div>
                </div>

                <button class="btn btn-primary">{{ __('main.write') }}</button>
            </form>
        </div>

    @else
        {!! showError(__('main.not_authorized')) !!}
    @endif
@stop
