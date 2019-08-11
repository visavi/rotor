@section('header')
    <h1>{{ trans('settings.offers') }}</h1>
@stop

<form action="/admin/settings?act=offers" method="post">
    @csrf
    <div class="form-group{{ hasError('sets[postoffers]') }}">
        <label for="postoffers">{{ trans('settings.offers_per_page') }}:</label>
        <input type="number" class="form-control" id="postoffers" name="sets[postoffers]" maxlength="2" value="{{ getInput('sets.postoffers', $settings['postoffers']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[postoffers]') }}</div>
    </div>

    <div class="form-group{{ hasError('sets[postcommoffers]') }}">
        <label for="postcommoffers">{{ trans('settings.offers_comments') }}:</label>
        <input type="number" class="form-control" id="postcommoffers" name="sets[postcommoffers]" maxlength="2" value="{{ getInput('sets.postcommoffers', $settings['postcommoffers']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[postcommoffers]') }}</div>
    </div>

    <div class="form-group{{ hasError('sets[addofferspoint]') }}">
        <label for="addofferspoint">{{ trans('settings.offers_points') }}:</label>
        <input type="number" class="form-control" id="addofferspoint" name="sets[addofferspoint]" maxlength="4" value="{{ getInput('sets.addofferspoint', $settings['addofferspoint']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[addofferspoint]') }}</div>
    </div>

    <button class="btn btn-primary">{{ trans('main.save') }}</button>
</form>
