@section('header')
    <h1>{{ __('settings.boards') }}</h1>
@stop

<form method="post">
    @csrf
    <div class="form-check mb-3">
        <input type="hidden" value="0" name="sets[boards_create]">
        <input type="checkbox" class="form-check-input" value="1" name="sets[boards_create]" id="boards_create"{{ getInput('sets.boards_create', $settings['boards_create']) ? ' checked' : '' }}>
        <label class="form-check-label" for="boards_create">{{ __('settings.boards_create') }}</label>
    </div>

    <div class="mb-3{{ hasError('sets[boards_period]') }}">
        <label for="boards_period" class="form-label">{{ __('settings.boards_period') }}:</label>
        <input type="number" class="form-control" id="boards_period" name="sets[boards_period]" min="1" value="{{ getInput('sets.boards_period', $settings['boards_period']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[boards_period]') }}</div>
    </div>

    <div class="mb-3{{ hasError('sets[boards_per_page]') }}">
        <label for="boards_per_page" class="form-label">{{ __('settings.boards_per_page') }}:</label>
        <input type="number" class="form-control" id="boards_per_page" name="sets[boards_per_page]" maxlength="2" value="{{ getInput('sets.boards_per_page', $settings['boards_per_page']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[boards_per_page]') }}</div>
    </div>

    <button class="btn btn-primary">{{ __('main.save') }}</button>
</form>
