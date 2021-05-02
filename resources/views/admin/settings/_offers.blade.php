@section('header')
    <h1>{{ __('settings.offers') }}</h1>
@stop

<form method="post">
    @csrf
    <div class="form-group{{ hasError('sets[postoffers]') }}">
        <label for="postoffers">{{ __('settings.offers_per_page') }}:</label>
        <input type="number" class="form-control" id="postoffers" name="sets[postoffers]" maxlength="2" value="{{ getInput('sets.postoffers', $settings['postoffers']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[postoffers]') }}</div>
    </div>

    <div class="form-group{{ hasError('sets[addofferspoint]') }}">
        <label for="addofferspoint">{{ __('settings.offers_points') }}:</label>
        <input type="number" class="form-control" id="addofferspoint" name="sets[addofferspoint]" maxlength="4" value="{{ getInput('sets.addofferspoint', $settings['addofferspoint']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[addofferspoint]') }}</div>
    </div>

    <button class="btn btn-primary">{{ __('main.save') }}</button>
</form>
