@section('header')
    <h1>{{ __('settings.votes') }}</h1>
@stop

<form method="post">
    @csrf
    <div class="mb-3{{ hasError('sets[allvotes]') }}">
        <label for="allvotes" class="form-label">{{ __('settings.votes_per_page') }}:</label>
        <input type="number" class="form-control" id="allvotes" name="sets[allvotes]" maxlength="2" value="{{ getInput('sets.allvotes', $settings['allvotes']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[allvotes]') }}</div>
    </div>

    <div class="mb-3">
        <label for="vote_title_min" class="form-label">{{ __('settings.vote_title_length') }}:</label>
        <div class="d-flex gap-2">
            <input type="number" class="form-control{{ hasError('sets[vote_title_min]') }}" id="vote_title_min" name="sets[vote_title_min]" value="{{ old('sets.vote_title_min', $settings['vote_title_min']) }}" placeholder="{{ __('main.min') }}" required>
            <input type="number" class="form-control{{ hasError('sets[vote_title_max]') }}" name="sets[vote_title_max]" value="{{ old('sets.vote_title_max', $settings['vote_title_max']) }}" placeholder="{{ __('main.max') }}" required>
        </div>
        <div class="invalid-feedback d-block">
            <div>{{ textError('sets[vote_title_min]') }}</div>
            <div>{{ textError('sets[vote_title_max]') }}</div>
        </div>
    </div>

    <div class="mb-3">
        <label for="vote_text_min" class="form-label">{{ __('settings.vote_text_length') }}:</label>
        <div class="d-flex gap-2">
            <input type="number" class="form-control{{ hasError('sets[vote_text_min]') }}" id="vote_text_min" name="sets[vote_text_min]" value="{{ old('sets.vote_text_min', $settings['vote_text_min']) }}" placeholder="{{ __('main.min') }}" required>
            <input type="number" class="form-control{{ hasError('sets[vote_text_max]') }}" name="sets[vote_text_max]" value="{{ old('sets.vote_text_max', $settings['vote_text_max']) }}" placeholder="{{ __('main.max') }}" required>
        </div>
        <div class="invalid-feedback d-block">
            <div>{{ textError('sets[vote_text_min]') }}</div>
            <div>{{ textError('sets[vote_text_max]') }}</div>
        </div>
    </div>

    <div class="mb-3">
        <label for="vote_answer_min" class="form-label">{{ __('settings.vote_answer_length') }}:</label>
        <div class="d-flex gap-2">
            <input type="number" class="form-control{{ hasError('sets[vote_answer_min]') }}" id="vote_answer_min" name="sets[vote_answer_min]" value="{{ old('sets.vote_answer_min', $settings['vote_answer_min']) }}" placeholder="{{ __('main.min') }}" required>
            <input type="number" class="form-control{{ hasError('sets[vote_answer_max]') }}" name="sets[vote_answer_max]" value="{{ old('sets.vote_answer_max', $settings['vote_answer_max']) }}" placeholder="{{ __('main.max') }}" required>
        </div>
        <div class="invalid-feedback d-block">
            <div>{{ textError('sets[vote_answer_min]') }}</div>
            <div>{{ textError('sets[vote_answer_max]') }}</div>
        </div>
    </div>

    <button class="btn btn-primary">{{ __('main.save') }}</button>
</form>
