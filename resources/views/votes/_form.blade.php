<div class="section-form mb-3 shadow">
    <form method="post">
        @csrf
        <div class="mb-3{{ hasError('question') }}">
            <label for="inputQuestion" class="form-label">{{ __('votes.question') }}:</label>
            <input type="text" name="question" class="form-control" id="inputQuestion" value="{{ getInput('question', $vote->title ?? null) }}" maxlength="100">
            <div class="invalid-feedback">{{ textError('question') }}</div>
        </div>

        <div class="mb-3{{ hasError('description') }}">
            <label for="text" class="form-label">{{ __('main.description') }}:</label>
            <textarea class="form-control markItUp" id="text" rows="5" name="description">{{ getInput('description', $vote->description ?? null) }}</textarea>
            <div class="invalid-feedback">{{ textError('description') }}</div>
        </div>

        @include('votes/_answers')

        <button class="btn btn-primary">{{ __('main.save') }}</button>
    </form>
</div>
