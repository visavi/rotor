@section('header')
    <h1>{{ __('settings.info') }}</h1>
@stop

<form method="post">
    @csrf
    <div class="form-check mb-3">
        <input type="hidden" value="0" name="sets[performance]">
        <input type="checkbox" class="form-check-input" value="1" name="sets[performance]" id="performance"{{ old('sets.performance', $settings['performance']) ? ' checked' : '' }}>
        <label class="form-check-label" for="performance">{{ __('settings.performance_enable') }}</label>
    </div>

    <div class="form-check mb-3">
        <input type="hidden" value="0" name="sets[onlines]">
        <input type="checkbox" class="form-check-input" value="1" name="sets[onlines]" id="onlines"{{ old('sets.onlines', $settings['onlines']) ? ' checked' : '' }}>
        <label class="form-check-label" for="onlines">{{ __('settings.online_enable') }}</label>
    </div>

    <div class="mb-3{{ hasError('sets[timeonline]') }}">
        <label for="timeonline" class="form-label">{{ __('settings.online_time') }}:</label>
        <input type="number" class="form-control" id="timeonline" name="sets[timeonline]" maxlength="3" value="{{ old('sets.timeonline', round($settings['timeonline'] / 60)) }}" required>
        <div class="invalid-feedback">{{ textError('sets[timeonline]') }}</div>

        <input type="hidden" value="60" name="mods[timeonline]">
    </div>

    <p class="text-muted fst-italic">
        {{ __('settings.online_time_hint') }}
    </p>

    <button class="btn btn-primary">{{ __('main.save') }}</button>
</form>
