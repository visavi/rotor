@extends('layout')

@section('title', __('users.edit_user') . ' ' . $user->getName())

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ __('index.panel') }}</a></li>
            <li class="breadcrumb-item"><a href="/admin/users">{{ __('index.users') }}</a></li>
            <li class="breadcrumb-item active">{{ __('users.edit_user') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <h3>
        {{ $user->getProfile() }}

        <small>
            @if ($user->login !== $user->getName())
                ({{ $user->login }})
            @endif
            #{{ $user->id }}
        </small>
    </h3>

    @if (getUser('id') === $user->id)
        <div class="alert alert-danger">{{ __('users.edit_user_notice') }}</div>
    @endif

    <div class="section-form mb-3 shadow">
        <nav>
            <div class="nav nav-tabs">
                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#nav-basic-fields" type="button">{{ __('users.basic_fields') }}</button>
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#nav-custom-fields" type="button">{{ __('users.custom_fields') }}</button>
            </div>
        </nav>

        <form method="post" action="/admin/users/edit?user={{ $user->login }}">
            @csrf

        <div class="tab-content">
            <div class="tab-pane fade show active py-3" id="nav-basic-fields">

            <?php $inputLevel = getInput('level', $user->level); ?>
            <div class="mb-3">
                <label for="level" class="form-label">{{ __('users.position') }}:</label>
                <select class="form-select" id="level" name="level">
                    @foreach ($allGroups as $key => $level)
                        <?php $selected = ($key === $inputLevel) ? ' selected' : ''; ?>
                        <option value="{{ $key }}"{{ $selected }}>{{ $level }}</option>
                    @endforeach
                </select>
                <div class="invalid-feedback">{{ textError('level') }}</div>
            </div>

            <div class="mb-3{{ hasError('password') }}">
                <label for="password" class="form-label">{{ __('users.new_password') }}:</label>
                <input type="text" class="form-control" id="password" name="password" maxlength="50" value="{{ getInput('password') }}">
                <div class="invalid-feedback">{{ textError('password') }}</div>
                <span class="text-muted fst-italic">{{ __('users.password_hint') }}</span>
            </div>

            <div class="mb-3{{ hasError('email') }}">
                <label for="email" class="form-label">{{ __('users.email') }}:</label>
                <input type="text" class="form-control" id="email" name="email" maxlength="50" value="{{ getInput('email', $user->email) }}" required>
                <div class="invalid-feedback">{{ textError('email') }}</div>
            </div>

            <div class="mb-3{{ hasError('name') }}">
                <label for="name" class="form-label">{{ __('users.name') }}:</label>
                <input type="text" class="form-control" id="name" name="name" maxlength="20" value="{{ getInput('name', $user->name) }}">
                <div class="invalid-feedback">{{ textError('name') }}</div>
            </div>


            <div class="mb-3{{ hasError('country') }}">
                <label for="country" class="form-label">{{ __('users.country') }}:</label>
                <input type="text" class="form-control" id="country" name="country" maxlength="30" value="{{ getInput('country', $user->country) }}">
                <div class="invalid-feedback">{{ textError('country') }}</div>
            </div>

            <div class="mb-3{{ hasError('city') }}">
                <label for="city" class="form-label">{{ __('users.city') }}:</label>
                <input type="text" class="form-control" id="city" name="city" maxlength="50" value="{{ getInput('city', $user->city) }}">
                <div class="invalid-feedback">{{ textError('city') }}</div>
            </div>

            <div class="mb-3{{ hasError('phone') }}">
                <label for="phone" class="form-label">{{ __('users.phone') }}:</label>
                <input class="phone form-control" id="phone" name="phone" placeholder="8 ___ ___-__-__" maxlength="18" value="{{ getInput('phone', $user->phone) }}">
                <div class="invalid-feedback">{{ textError('phone') }}</div>
            </div>

            <div class="mb-3{{ hasError('site') }}">
                <label for="site" class="form-label">{{ __('users.site') }}:</label>
                <input type="text" class="form-control" id="site" name="site" maxlength="50" value="{{ getInput('site', $user->site) }}">
                <div class="invalid-feedback">{{ textError('site') }}</div>
            </div>

            <div class="mb-3{{ hasError('created') }}">
                <label for="created" class="form-label">{{ __('users.registered') }}:</label>
                <input type="text" class="form-control" id="created" name="created" maxlength="10" value="{{ getInput('created', dateFixed($user->created_at, 'd.m.Y', true)) }}" required>
                <div class="invalid-feedback">{{ textError('created') }}</div>
            </div>

            <div class="mb-3{{ hasError('birthday') }}">
                <label for="birthday" class="form-label">{{ __('users.birthday') }}:</label>
                <input type="text" class="form-control" id="birthday" name="birthday" maxlength="10" value="{{ getInput('birthday', $user->birthday) }}">
                <div class="invalid-feedback">{{ textError('birthday') }}</div>
            </div>

            <div class="mb-3{{ hasError('point') }}">
                <label for="point" class="form-label">{{ __('users.assets') }}:</label>
                <input type="text" class="form-control" id="point" name="point" maxlength="10" value="{{ getInput('point', $user->point) }}" required>
                <div class="invalid-feedback">{{ textError('point') }}</div>
            </div>

            <div class="mb-3{{ hasError('money') }}">
                <label for="money" class="form-label">{{ __('users.moneys') }}:</label>
                <input type="text" class="form-control" id="money" name="money" maxlength="15" value="{{ getInput('money', $user->money) }}" required>
                <div class="invalid-feedback">{{ textError('money') }}</div>
            </div>

            <div class="mb-3{{ hasError('status') }}">
                <label for="status" class="form-label">{{ __('users.status') }}:</label>
                <input type="text" class="form-control" id="status" name="status" maxlength="25" value="{{ getInput('status', $user->status) }}">
                <div class="invalid-feedback">{{ textError('status') }}</div>
            </div>

            <div class="mb-3{{ hasError('posrating') }}">
                <label for="posrating" class="form-label">{{ __('users.reputation') }} ({{ __('main.pluses') }}):</label>
                <input type="text" class="form-control" id="posrating" name="posrating" maxlength="10" value="{{ getInput('posrating', $user->posrating) }}" required>
                <div class="invalid-feedback">{{ textError('posrating') }}</div>
            </div>

            <div class="mb-3{{ hasError('negrating') }}">
                <label for="negrating" class="form-label">{{ __('users.reputation') }} ({{ __('main.minuses') }}):</label>
                <input type="text" class="form-control" id="negrating" name="negrating" maxlength="10" value="{{ getInput('negrating', $user->negrating) }}" required>
                <div class="invalid-feedback">{{ textError('negrating') }}</div>
            </div>

            <?php $inputThemes = getInput('themes', $user->themes); ?>
            <div class="mb-3{{ hasError('themes') }}">
                <label for="themes" class="form-label">{{ __('users.theme') }}:</label>

                <select class="form-select" name="themes" id="themes">
                    @foreach ($allThemes as $theme)
                        <?php $selected = ($theme === $inputThemes) ? ' selected' : ''; ?>
                        <option value="{{ $theme }}"{{ $selected }}>{{ $theme }}</option>
                    @endforeach
                </select>

                <div class="invalid-feedback">{{ textError('themes') }}</div>
            </div>

            <?php $inputGender = getInput('gender', $user->gender); ?>
            {{ __('users.gender') }}:
            <div class="mb-3{{ hasError('gender') }}">
                <div class="form-check">
                    <input class="form-check-input" type="radio" id="inputGenderMale" name="gender" value="male"{{ $inputGender === 'male' ? ' checked' : '' }}>
                    <label class="form-check-label" for="inputGenderMale">{{ __('main.male') }}</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" id="inputGenderFemale" name="gender" value="female"{{ $inputGender === 'female' ? ' checked' : '' }}>
                    <label class="form-check-label" for="inputGenderFemale">{{ __('main.female') }}</label>
                </div>
                <div class="invalid-feedback">{{ textError('gender') }}</div>
            </div>

            <div class="mb-3{{ hasError('info') }}">
                <label for="info" class="form-label">{{ __('users.about') }}:</label>
                <textarea class="form-control markItUp" id="info" rows="5" name="info">{{ getInput('info', $user->info) }}</textarea>
                <div class="invalid-feedback">{{ textError('info') }}</div>
            </div>

             </div>
            <div class="tab-pane fade py-3" id="nav-custom-fields">
                @forelse($fields as $field)
                    <div class="mb-3{{ $field->required ? ' form-required' : null }}{{ hasError('field' . $field->id) }}">
                        <label for="{{ 'field' . $field->id }}" class="form-label">{{ $field->name }}:</label>
                        @if ($field->type === 'textarea')
                            <textarea class="form-control markItUp" id="{{ 'field' . $field->id }}" cols="25" rows="5" name="{{ 'field' . $field->id }}">{{ getInput('field' . $field->id, $field->value) }}</textarea>
                        @else
                            <input class="form-control" id="{{ 'field' . $field->id }}" name="{{ 'field' . $field->id }}" maxlength="{{ $field->length }}" value="{{ getInput('field' . $field->id, $field->value) }}">
                        @endif
                        <div class="invalid-feedback">{{ textError('field' . $field->id) }}</div>
                    </div>
                @empty
                    <div class="alert alert-danger">
                        {{ __('users.empty_custom_fields') }}
                    </div>
                @endforelse
            </div>
        </div>

            <button class="btn btn-primary">{{ __('main.change') }}</button>
        </form>
    </div>

    <div class="bg-info text-white p-1 my-1">
        <b>{{ __('users.additional_info') }}</b>
    </div>

    @if ($user->level === 'pended')
        <div class="p-1 my-1 bg-danger text-white">
            <i class="fas fa-exclamation-triangle"></i>
            {{ __('users.user_not_active') }}
        </div>
    @endif

    @if ($user->level === 'banned' && $user->timeban > SITETIME)
        <div class="section-form mb-3 shadow">
            <div class="p-1 my-1 bg-danger text-white">{{ __('users.user_banned') }}</div>
            {{ __('users.ending_ban') }}: {{ formatTime($user->timeban - SITETIME) }}<br>

            @if ($banhist)
                {{ __('users.term') }}: {{ formatTime($banhist->term) }}<br>
                {{ __('users.reason_ban') }}: {{ bbCode($banhist->reason) }}<br>
                {{ __('users.banned') }}: {{ $banhist->sendUser->getProfile() }}<br>
            @endif
        </div>
    @endif

    {{ __('users.last_visit') }}: {{ $user->getVisit() }}<br><br>

    @if (! in_array($user->level, $adminGroups, true))
        <i class="fa fa-times"></i> <a href="/admin/users/delete?user={{ $user->login }}">{{ __('main.delete') }}</a><br>
    @endif
@stop
