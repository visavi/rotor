@extends('layout')

@section('title')
    {{ trans('index.my_profile') }}
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/users/{{ $user->login }}">{{ $user->login }}</a></li>
            <li class="breadcrumb-item active">{{ trans('index.my_profile') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <div class="form">
        <form method="post" action="/profile">
            @csrf
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-6">

                        <div class="form-group{{ hasError('name') }}">
                            <label for="inputName">{{ trans('users.name') }}:</label>
                            <input class="form-control" id="inputName" name="name" maxlength="20" value="{{ getInput('name', $user->name) }}">
                            {!! textError('name') !!}
                        </div>

                        <div class="form-group{{ hasError('country') }}">
                            <label for="inputCountry">{{ trans('users.country') }}:</label>
                            <input class="form-control" id="inputCountry" name="country" maxlength="30" value="{{ getInput('country', $user->country) }}">
                            {!! textError('country') !!}
                        </div>

                        <div class="form-group{{ hasError('city') }}">
                            <label for="inputCity">{{ trans('users.city') }}:</label>
                            <input class="form-control" id="inputCity" name="city" maxlength="50" value="{{ getInput('city', $user->city) }}">
                            {!! textError('city') !!}
                        </div>

                        <div class="form-group{{ hasError('phone') }}">
                            <label for="inputPhone">{{ trans('users.phone') }}:</label>
                            <input class="phone form-control" id="inputPhone" name="phone" placeholder="8 ___ ___-__-__" maxlength="15" value="{{ getInput('phone', $user->phone) }}">
                            {!! textError('phone') !!}
                        </div>

                        <div class="form-group{{ hasError('icq') }}">
                            <label for="inputIcq">ICQ:</label>
                            <input class="form-control" id="inputIcq" name="icq" maxlength="10" value="{{ getInput('icq', $user->icq) }}">
                            {!! textError('icq') !!}
                        </div>

                        <div class="form-group{{ hasError('skype') }}">
                            <label for="inputSkype">Skype:</label>
                            <input class="form-control" id="inputSkype" name="skype" maxlength="32" value="{{ getInput('skype', $user->skype) }}">
                            {!! textError('skype') !!}
                        </div>

                        <div class="form-group{{ hasError('site') }}">
                            <label for="inputSite">{{ trans('users.site') }}:</label>
                            <input class="form-control" id="inputSite" name="site" maxlength="50" value="{{ getInput('site', $user->site) }}">
                            {!! textError('site') !!}
                        </div>


                        <div class="form-group{{ hasError('birthday') }}">
                            <label for="inputBirthday">{{ trans('users.birthday') }} (dd.mm.yyyy):</label>
                            <input class="form-control" id="inputBirthday" name="birthday" maxlength="10" value="{{ getInput('birthday', $user->birthday) }}">
                            {!! textError('birthday') !!}
                        </div>

                        <?php $inputGender = getInput('gender', $user->gender); ?>
                        {{ trans('users.gender') }}:
                        <div class="form-group{{ hasError('gender') }}">
                            <div class="custom-control custom-radio">
                                <input class="custom-control-input" type="radio" id="inputGenderMale" name="gender" value="male"{{ $inputGender === 'male' ? ' checked' : '' }}>
                                <label class="custom-control-label" for="inputGenderMale">{{ trans('main.male') }}</label>
                            </div>
                            <div class="custom-control custom-radio">
                                <input class="custom-control-input" type="radio" id="inputGenderFemale" name="gender" value="female"{{ $inputGender === 'female' ? ' checked' : '' }}>
                                <label class="custom-control-label" for="inputGenderFemale">{{ trans('main.female') }}</label>
                            </div>
                            {!! textError('gender') !!}
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="float-right">
                            @if ($user->picture && file_exists(HOME . $user->picture))
                                <a class="gallery" href="{{ getUser('picture') }}">
                                    {!! resizeImage(getUser('picture'), ['alt' => $user->login, 'class' => 'img-fluid rounded']) !!}
                                </a><br>
                                <a href="/pictures">{{ trans('main.change') }}</a> / <a href="/pictures/delete?token={{ $_SESSION['token'] }}">{{ trans('main.delete') }}</a>
                            @else
                                <img class="img-fluid rounded" src="/assets/img/images/photo.jpg" alt="Photo"><br>
                                <a href="/pictures">{{ trans('main.upload') }}</a>
                            @endif
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="form-group{{ hasError('info') }}">
                            <label for="info">{{ trans('users.about') }}:</label>
                            <textarea class="form-control markItUp" id="info" cols="25" rows="5" name="info">{{ getInput('info', $user->info) }}</textarea>
                            {!! textError('info') !!}
                        </div>
                        <button class="btn btn-primary">{{ trans('main.change') }}</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
@stop
