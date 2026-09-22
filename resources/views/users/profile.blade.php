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

@php
$hasPicture   = $user->picture && file_exists(public_path($user->picture));
$inputGender  = old('gender', $user->gender);
@endphp

@section('content')
    <div class="section-form mb-3 shadow">
        <div class="section-title"><i class="fas fa-image"></i> {{ __('users.photo') }}</div>

        <div class="profile-photo">
            <div class="profile-avatar">
                @if ($hasPicture)
                    <a href="{{ $user->picture }}" data-fancybox="gallery">
                        <img src="{{ $user->picture }}" alt="{{ $user->login }}">
                    </a>
                @else
                    <img src="/assets/img/images/photo.svg" alt="Photo">
                @endif
            </div>

            <div>
                @if ($hasPicture)
                    <a class="btn btn-sm btn-adaptive" href="/pictures"><i class="fas fa-rotate"></i> {{ __('main.change') }}</a>
                    <button class="btn btn-sm btn-danger" form="delete-picture-form"><i class="fas fa-trash"></i> {{ __('main.delete') }}</button>
                @else
                    <a class="btn btn-sm btn-primary" href="/pictures"><i class="fas fa-upload"></i> {{ __('main.upload') }}</a>
                @endif
            </div>
        </div>
    </div>

    <form method="post" action="/profile">
        @csrf

        <div class="section-form mb-3 shadow">
            <div class="section-title"><i class="fas fa-id-card"></i> {{ __('index.my_profile') }}</div>

            <div class="row">
                <div class="col-md-6 mb-3{{ hasError('name') }}">
                    <label for="inputName" class="form-label">{{ __('users.name') }}:</label>
                    <input class="form-control" id="inputName" name="name" maxlength="20" value="{{ old('name', $user->name) }}">
                    <div class="invalid-feedback">{{ textError('name') }}</div>
                </div>

                <div class="col-md-6 mb-3{{ hasError('birthday') }}">
                    <label for="inputBirthday" class="form-label">{{ __('users.birthday') }} (dd.mm.yyyy):</label>
                    <input class="birthday form-control" id="inputBirthday" name="birthday" maxlength="10" value="{{ old('birthday', $user->birthday) }}">
                    <div class="invalid-feedback">{{ textError('birthday') }}</div>
                </div>

                <div class="col-md-6 mb-3{{ hasError('country') }}">
                    <label for="inputCountry" class="form-label">{{ __('users.country') }}:</label>
                    <input class="form-control" id="inputCountry" name="country" maxlength="30" value="{{ old('country', $user->country) }}">
                    <div class="invalid-feedback">{{ textError('country') }}</div>
                </div>

                <div class="col-md-6 mb-3{{ hasError('city') }}">
                    <label for="inputCity" class="form-label">{{ __('users.city') }}:</label>
                    <input class="form-control" id="inputCity" name="city" maxlength="50" value="{{ old('city', $user->city) }}">
                    <div class="invalid-feedback">{{ textError('city') }}</div>
                </div>

                <div class="col-md-6 mb-3{{ hasError('phone') }}">
                    <label for="inputPhone" class="form-label">{{ __('users.phone') }}:</label>
                    <input class="phone form-control" id="inputPhone" name="phone" placeholder="+7 ___ ___-__-__" maxlength="18" value="{{ old('phone', $user->phone) }}">
                    <div class="form-text">{{ __('users.phone_hidden') }}</div>
                    <div class="invalid-feedback">{{ textError('phone') }}</div>
                </div>

                <div class="col-md-6 mb-3{{ hasError('site') }}">
                    <label for="inputSite" class="form-label">{{ __('users.site') }}:</label>
                    <input class="form-control" id="inputSite" name="site" maxlength="50" value="{{ old('site', $user->site) }}">
                    <div class="invalid-feedback">{{ textError('site') }}</div>
                </div>

                <div class="col-md-6 mb-3{{ hasError('gender') }}">
                    <label class="form-label">{{ __('users.gender') }}:</label>

                    <div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" id="inputGenderMale" name="gender" value="male"{{ $inputGender === 'male' ? ' checked' : '' }}>
                            <label class="form-check-label" for="inputGenderMale"><i class="fa fa-male"></i> {{ __('main.male') }}</label>
                        </div>

                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" id="inputGenderFemale" name="gender" value="female"{{ $inputGender === 'female' ? ' checked' : '' }}>
                            <label class="form-check-label" for="inputGenderFemale"><i class="fa fa-female"></i> {{ __('main.female') }}</label>
                        </div>
                    </div>

                    <div class="invalid-feedback">{{ textError('gender') }}</div>
                </div>
            </div>
        </div>

        <div class="section-form mb-3 shadow">
            <div class="section-title"><i class="fas fa-circle-info"></i> {{ __('users.about') }}</div>

            <div class="mb-3{{ hasError('info') }}">
                <textarea class="form-control tiptap" id="info" cols="25" rows="5" name="info">{{ old('info', $user->info) }}</textarea>
                <div class="invalid-feedback">{{ textError('info') }}</div>
            </div>

            @hook('profileFields', $user)
        </div>

        <button class="btn btn-primary">{{ __('main.change') }}</button>
    </form>

    <form id="delete-picture-form" action="/pictures/delete" method="post" class="d-none">
        @csrf
        @method('DELETE')
    </form>
@stop
