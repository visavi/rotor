@extends('layout')

@section('title', __('index.my_profile'))

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/users/{{ $user->login }}">{{ $user->getName() }}</a></li>
            <li class="breadcrumb-item active">{{ __('index.my_profile') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <div class="section-form p-3 shadow">
        <form method="post" action="/profile">
            @csrf
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-6">

                        <div class="form-group{{ hasError('name') }}">
                            <label for="inputName">{{ __('users.name') }}:</label>
                            <input class="form-control" id="inputName" name="name" maxlength="20" value="{{ getInput('name', $user->name) }}">
                            <div class="invalid-feedback">{{ textError('name') }}</div>
                        </div>

                        <div class="form-group{{ hasError('country') }}">
                            <label for="inputCountry">{{ __('users.country') }}:</label>
                            <input class="form-control" id="inputCountry" name="country" maxlength="30" value="{{ getInput('country', $user->country) }}">
                            <div class="invalid-feedback">{{ textError('country') }}</div>
                        </div>

                        <div class="form-group{{ hasError('city') }}">
                            <label for="inputCity">{{ __('users.city') }}:</label>
                            <input class="form-control" id="inputCity" name="city" maxlength="50" value="{{ getInput('city', $user->city) }}">
                            <div class="invalid-feedback">{{ textError('city') }}</div>
                        </div>

                        <div class="form-group{{ hasError('phone') }}">
                            <label for="inputPhone">{{ __('users.phone') }}:</label>
                            <input class="phone form-control" id="inputPhone" name="phone" placeholder="8 ___ ___-__-__" maxlength="15" value="{{ getInput('phone', $user->phone) }}">
                            <div class="invalid-feedback">{{ textError('phone') }}</div>
                        </div>

                        <div class="form-group{{ hasError('site') }}">
                            <label for="inputSite">{{ __('users.site') }}:</label>
                            <input class="form-control" id="inputSite" name="site" maxlength="50" value="{{ getInput('site', $user->site) }}">
                            <div class="invalid-feedback">{{ textError('site') }}</div>
                        </div>


                        <div class="form-group{{ hasError('birthday') }}">
                            <label for="inputBirthday">{{ __('users.birthday') }} (dd.mm.yyyy):</label>
                            <input class="birthday form-control" id="inputBirthday" name="birthday" maxlength="10" value="{{ getInput('birthday', $user->birthday) }}">
                            <div class="invalid-feedback">{{ textError('birthday') }}</div>
                        </div>

                        <?php $inputGender = getInput('gender', $user->gender); ?>
                        {{ __('users.gender') }}:
                        <div class="form-group{{ hasError('gender') }}">
                            <div class="custom-control custom-radio">
                                <input class="custom-control-input" type="radio" id="inputGenderMale" name="gender" value="male"{{ $inputGender === 'male' ? ' checked' : '' }}>
                                <label class="custom-control-label" for="inputGenderMale">{{ __('main.male') }}</label>
                            </div>
                            <div class="custom-control custom-radio">
                                <input class="custom-control-input" type="radio" id="inputGenderFemale" name="gender" value="female"{{ $inputGender === 'female' ? ' checked' : '' }}>
                                <label class="custom-control-label" for="inputGenderFemale">{{ __('main.female') }}</label>
                            </div>
                            <div class="invalid-feedback">{{ textError('gender') }}</div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="float-right">
                            @if ($user->picture && file_exists(HOME . $user->picture))
                                <a class="gallery" href="{{ getUser('picture') }}">
                                    {!! resizeImage(getUser('picture'), ['alt' => $user->login, 'class' => 'img-fluid rounded']) !!}
                                </a><br>
                                <a href="/pictures">{{ __('main.change') }}</a> / <a href="/pictures/delete?token={{ $_SESSION['token'] }}">{{ __('main.delete') }}</a>
                            @else
                                <img class="img-fluid rounded" src="/assets/img/images/photo.png" alt="Photo"><br>
                                <a href="/pictures">{{ __('main.upload') }}</a>
                            @endif
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="form-group{{ hasError('info') }}">
                            <label for="info">{{ __('users.about') }}:</label>
                            <textarea class="form-control markItUp" id="info" cols="25" rows="5" name="info">{{ getInput('info', $user->info) }}</textarea>
                            <div class="invalid-feedback">{{ textError('info') }}</div>
                        </div>
                        <button class="btn btn-primary">{{ __('main.change') }}</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
@stop
