@extends('layout')

@section('title', __('users.delete_user') . ' ' . $user->getName())

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">{{ __('index.panel') }}</a></li>
            <li class="breadcrumb-item"><a href="/admin/users">{{ __('index.users') }}</a></li>
            <li class="breadcrumb-item"><a href="/admin/users/edit?user={{ $user->login }}">{{ $user->getName() }}</a></li>
            <li class="breadcrumb-item active">{{ __('users.delete_user') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <div class="section-form mb-3 shadow">
        <form action="/admin/users/delete?user={{ $user->login }}" method="post">
            @csrf
            <b>{{ __('users.add_to_blacklist') }}:</b><br>
            <div class="form-check">
                <input type="checkbox" class="form-check-input" value="1" name="loginblack" id="loginblack" checked>
                <label class="form-check-label" for="loginblack">{{ __('users.login') }}</label>
            </div>

            <div class="form-check">
                <input type="checkbox" class="form-check-input" value="1" name="mailblack" id="mailblack" checked>
                <label class="form-check-label" for="mailblack">{{ __('users.email') }}</label>
            </div>

            <b>{{ __('users.delete_activity') }}:</b><br>

            <div class="form-check">
                <input type="checkbox" class="form-check-input" value="1" name="deltopics" id="deltopics">
                <label class="form-check-label" for="deltopics">{{ __('users.forum_topics') }}</label>
            </div>

            <div class="form-check">
                <input type="checkbox" class="form-check-input" value="1" name="delposts" id="delposts">
                <label class="form-check-label" for="delposts">{{ __('users.forum_posts') }}</label>
            </div>

            <div class="form-check">
                <input type="checkbox" class="form-check-input" value="1" name="delcomments" id="delcomments">
                <label class="form-check-label" for="delcomments">{{ __('main.comments') }}</label>
            </div>

            <div class="form-check">
                <input type="checkbox" class="form-check-input" value="1" name="delimages" id="delimages">
                <label class="form-check-label" for="delimages">{{ __('users.photos') }}</label>
            </div>

            <button class="btn btn-danger">{{ __('main.delete') }}</button>
        </form>
    </div>
@stop
