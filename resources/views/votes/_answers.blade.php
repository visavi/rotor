<div class="mb-3{{ hasError('answers') }}">
    <div class="js-answer-list">
        @php
            $answers = array_diff((array) getInput('answers', $vote->getAnswers ?? []), ['']);
            $answers = array_pad(array_unique($answers), 2, '');
        @endphp

        <label for="answers0" class="form-label">{{ __('votes.options') }}:</label>
        <a class="js-answer-add" href="#" data-bs-toggle="tooltip" title="{{ __('main.add') }}"><i class="fas fa-plus-square"></i></a>

        @foreach ($answers as $key => $answer)
            @if ($loop->index < max(2, count($vote->getAnswers ?? [])))
                <div class="input-group mt-1">
                    <input type="text" name="answers[{{ $key }}]" class="form-control" id="answers{{ $loop->index }}" value="{{ $answer }}" maxlength="50" placeholder="{{ __('votes.answer') }}">
                </div>
            @else
                <div class="input-group mt-1 js-answer-append">
                    <input class="form-control" name="answers[{{ $key }}]" type="text" value="{{ $answer }}" maxlength="50" placeholder="{{ __('votes.answer') }}">
                    <span class="input-group-text">
                        <a class="js-answer-remove" href="#"><i class="fa fa-times"></i></a>
                    </span>
                </div>
            @endif
        @endforeach
    </div>
    <span class="text-muted fst-italic">{{ __('votes.hint_answers') }}</span>
    <div class="invalid-feedback">{{ textError('answers') }}</div>
</div>

@push('scripts')
    <script type="module">
        const answerList = document.querySelector('.js-answer-list');

        document.querySelector('.js-answer-add')?.addEventListener('click', function (e) {
            e.preventDefault();
            if (answerList.querySelectorAll('input').length >= 10) {
                notyf.error("{{ __('votes.hint_answers') }}");
                return;
            }
            answerList.insertAdjacentHTML('beforeend',
                '<div class="input-group mt-1 js-answer-append">' +
                '<input class="form-control" name="answers[]" type="text" value="" maxlength="50" placeholder="<?= __('votes.answer') ?>">' +
                '<span class="input-group-text">' +
                '<a class="js-answer-remove" href="#"><i class="fa fa-times"></i></a>' +
                '</span>' +
                '</div>');
        });

        document.addEventListener('click', function (e) {
            const btn = e.target.closest('.js-answer-remove');
            if (!btn) return;
            e.preventDefault();
            btn.closest('.js-answer-append').remove();
        });
    </script>
@endpush
