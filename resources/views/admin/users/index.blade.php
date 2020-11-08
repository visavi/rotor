@extends('layout')

@section('title', __('index.users'))

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ __('index.panel') }}</a></li>
            <li class="breadcrumb-item active">{{ __('index.users') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <div class="section-form shadow">
        <form action="/admin/users/edit" method="get">
            <div class="form-inline">
                <div class="form-group{{ hasError('user') }}">
                    <input type="text" class="form-control" id="user" name="user" maxlength="20" value="{{ getInput('user') }}" placeholder="{{ __('main.user_login') }}" required>
                </div>

                <button class="btn btn-primary">{{ __('main.edit') }}</button>
            </div>
            <div class="invalid-feedback">{{ textError('user') }}</div>
        </form>
    </div>

    <a class="badge badge-pill badge-success" href="/admin/users/search?q=1">0-9</a>
    <a class="badge badge-pill badge-success" href="/admin/users/search?q=a">A</a>
    <a class="badge badge-pill badge-success" href="/admin/users/search?q=b">B</a>
    <a class="badge badge-pill badge-success" href="/admin/users/search?q=c">C</a>
    <a class="badge badge-pill badge-success" href="/admin/users/search?q=d">D</a>
    <a class="badge badge-pill badge-success" href="/admin/users/search?q=e">E</a>
    <a class="badge badge-pill badge-success" href="/admin/users/search?q=f">F</a>
    <a class="badge badge-pill badge-success" href="/admin/users/search?q=g">G</a>
    <a class="badge badge-pill badge-success" href="/admin/users/search?q=h">H</a>
    <a class="badge badge-pill badge-success" href="/admin/users/search?q=i">I</a>
    <a class="badge badge-pill badge-success" href="/admin/users/search?q=j">J</a>
    <a class="badge badge-pill badge-success" href="/admin/users/search?q=k">K</a>
    <a class="badge badge-pill badge-success" href="/admin/users/search?q=l">L</a>
    <a class="badge badge-pill badge-success" href="/admin/users/search?q=m">M</a>
    <a class="badge badge-pill badge-success" href="/admin/users/search?q=n">N</a>
    <a class="badge badge-pill badge-success" href="/admin/users/search?q=o">O</a>
    <a class="badge badge-pill badge-success" href="/admin/users/search?q=p">P</a>
    <a class="badge badge-pill badge-success" href="/admin/users/search?q=q">Q</a>
    <a class="badge badge-pill badge-success" href="/admin/users/search?q=r">R</a>
    <a class="badge badge-pill badge-success" href="/admin/users/search?q=s">S</a>
    <a class="badge badge-pill badge-success" href="/admin/users/search?q=t">T</a>
    <a class="badge badge-pill badge-success" href="/admin/users/search?q=u">U</a>
    <a class="badge badge-pill badge-success" href="/admin/users/search?q=v">V</a>
    <a class="badge badge-pill badge-success" href="/admin/users/search?q=w">W</a>
    <a class="badge badge-pill badge-success" href="/admin/users/search?q=x">X</a>
    <a class="badge badge-pill badge-success" href="/admin/users/search?q=y">Y</a>
    <a class="badge badge-pill badge-success" href="/admin/users/search?q=z">Z</a>
    <br><br>

    <h3>{{ __('users.last_registered') }}</h3>

    @if ($users->isNotEmpty())
        @foreach ($users as $user)
            <div  class="text-truncate bg-light my-1">
                <div class="img">
                    {!! $user->getAvatar() !!}
                    {!! $user->getOnline() !!}
                </div>

                <b><a href="/admin/users/edit?user={{ $user->login }}">{{ $user->getName() }}</a></b>
                ({{ __('users.email') }}: {{ $user->email }})<br>
                {{ __('users.registered') }}: {{ dateFixed($user->created_at, 'd.m.Y') }}
            </div>
        @endforeach
    @else
        {!! showError(__('main.empty_users')) !!}
    @endif

    {{ $users->links() }}
@stop
