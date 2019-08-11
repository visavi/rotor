@section('header')
    <h1>{{ trans('settings.info') }}</h1>
@stop

<form action="/admin/settings?act=info" method="post">
    @csrf
    <?php $inputCounter = (int) getInput('sets.incount', $settings['incount']); ?>
    <div class="form-group{{ hasError('sets[incount]') }}">
        <label for="incount">{{ trans('settings.counters_enable') }}:</label>
        <select class="form-control" id="incount" name="sets[incount]">

            @foreach ($counters as $key => $counter)
                <?php $selected = ($key === $inputCounter) ? ' selected' : ''; ?>
                <option value="{{ $key }}"{{ $selected }}>{{ $counter }}</option>
            @endforeach

        </select>
        <div class="invalid-feedback">{{ textError('sets[incount]') }}</div>
    </div>

    <div class="custom-control custom-checkbox">
        <input type="hidden" value="0" name="sets[performance]">
        <input type="checkbox" class="custom-control-input" value="1" name="sets[performance]" id="performance"{{ getInput('sets.performance', $settings['performance']) ? ' checked' : '' }}>
        <label class="custom-control-label" for="performance">{{ trans('settings.performance_enable') }}</label>
    </div>

    <div class="custom-control custom-checkbox">
        <input type="hidden" value="0" name="sets[onlines]">
        <input type="checkbox" class="custom-control-input" value="1" name="sets[onlines]" id="onlines"{{ getInput('sets.onlines', $settings['onlines']) ? ' checked' : '' }}>
        <label class="custom-control-label" for="onlines">{{ trans('settings.online_enable') }}</label>
    </div>

    <div class="form-group{{ hasError('sets[timeonline]') }}">
        <label for="timeonline">{{ trans('settings.online_time') }}:</label>
        <input type="number" class="form-control" id="timeonline" name="sets[timeonline]" maxlength="3" value="{{ getInput('sets.timeonline', round($settings['timeonline'] / 60)) }}" required>
        <div class="invalid-feedback">{{ textError('sets[timeonline]') }}</div>

        <input type="hidden" value="60" name="mods[timeonline]">
    </div>

    <p class="text-muted font-italic">
        {{ trans('settings.online_time_hint') }}
    </p>

    <button class="btn btn-primary">{{ trans('main.save') }}</button>
</form>
