@extends('layout')

@section('title')
    {{ trans('users.delete_user') }} {{ $user->login }}
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ trans('index.panel') }}</a></li>
            <li class="breadcrumb-item"><a href="/admin/users">{{ trans('index.users') }}</a></li>
            <li class="breadcrumb-item"><a href="/admin/users/edit?user={{ $user->login }}">{{ $user->login }}</a></li>
            <li class="breadcrumb-item active">{{ trans('users.delete_user') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <div class="form">
        <form action="/admin/users/delete?user={{ $user->login }}" method="post">
            @csrf
            <b>{{ trans('users.add_to_blacklist') }}:</b><br>
            <div class="custom-control custom-checkbox">
                <input type="checkbox" class="custom-control-input" value="1" name="loginblack" id="loginblack" checked>
                <label class="custom-control-label" for="loginblack">{{ trans('users.login') }}</label>
            </div>

            <div class="custom-control custom-checkbox">
                <input type="checkbox" class="custom-control-input" value="1" name="mailblack" id="mailblack" checked>
                <label class="custom-control-label" for="mailblack">{{ trans('users.email') }}</label>
            </div>

            <b>{{ trans('users.delete_activity') }}:</b><br>

            <div class="custom-control custom-checkbox">
                <input type="checkbox" class="custom-control-input" value="1" name="deltopics" id="deltopics">
                <label class="custom-control-label" for="deltopics">{{ trans('users.forum_topics') }}</label>
            </div>

            <div class="custom-control custom-checkbox">
                <input type="checkbox" class="custom-control-input" value="1" name="delposts" id="delposts">
                <label class="custom-control-label" for="delposts">{{ trans('users.forum_posts') }}</label>
            </div>

            <div class="custom-control custom-checkbox">
                <input type="checkbox" class="custom-control-input" value="1" name="delcomments" id="delcomments">
                <label class="custom-control-label" for="delcomments">{{ trans('main.comments') }}</label>
            </div>

            <div class="custom-control custom-checkbox">
                <input type="checkbox" class="custom-control-input" value="1" name="delimages" id="delimages">
                <label class="custom-control-label" for="delimages">{{ trans('users.photos') }}</label>
            </div>

            <button class="btn btn-danger">{{ trans('main.delete') }}</button>
        </form>
    </div>
@stop
