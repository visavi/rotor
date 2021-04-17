<div class="section-form mb-3 shadow">
    <form method="post">
        @csrf
        <div class="form-group{{ hasError('question') }}">
            <label for="inputQuestion">{{ __('votes.question') }}:</label>
            <input type="text" name="question" class="form-control" id="inputQuestion" value="{{ getInput('question', $vote->title ?? null) }}" maxlength="100">
            <div class="invalid-feedback">{{ textError('question') }}</div>
        </div>

        <div class="form-group{{ hasError('description') }}">
            <label for="text">{{ __('main.description') }}:</label>
            <textarea class="form-control markItUp" id="text" rows="5" name="description">{{ getInput('description', $vote->description ?? null) }}</textarea>
            <div class="invalid-feedback">{{ textError('description') }}</div>
        </div>

        @include('votes/_answers')

        <button class="btn btn-primary">{{ __('main.save') }}</button>
    </form>
</div>
