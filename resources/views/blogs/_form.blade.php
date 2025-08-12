<form method="post">
    @csrf
    <div class="mb-3{{ hasError('cid') }}">
        <label for="inputCategory" class="form-label">{{ __('blogs.blog') }}</label>

        <?php $inputCategory = (int) getInput('cid', $article->category_id ?? $cid); ?>
        <select class="form-select" id="inputCategory" name="cid">
            @foreach ($categories as $category)
                <option value="{{ $category->id }}"{{ ($inputCategory === $category->id && ! $category->closed) ? ' selected' : '' }}{{ $category->closed ? ' disabled' : '' }}>
                    {{ str_repeat('–', $category->depth) }} {{ $category->name }}
                </option>
            @endforeach
        </select>
        <div class="invalid-feedback">{{ textError('cid') }}</div>
    </div>

    <div class="mb-3{{ hasError('title') }}">
        <label for="inputTitle" class="form-label">{{ __('blogs.name') }}:</label>
        <input type="text" class="form-control" id="inputTitle" name="title" maxlength="{{ setting('blog_title_max') }}" value="{{ getInput('title', $article->title) }}" required>
        <div class="invalid-feedback">{{ textError('title') }}</div>
    </div>

    <div class="mb-3{{ hasError('text') }}">
        <label for="text" class="form-label">{{ __('blogs.article') }}:</label>
        <textarea class="form-control markItUp" maxlength="{{ setting('blog_text_max') }}" id="text" rows="5" name="text" required>{{ getInput('text', $article->text) }}</textarea>
        <div class="invalid-feedback">{{ textError('text') }}</div>
        <span class="js-textarea-counter"></span>
    </div>

    <?php $inputTags = getInput('tags', $article->tags->pluck('name') ?? []); ?>
    <div class="mb-3{{ hasError('tags') }}">
        <label for="tags" class="form-label">{{ __('blogs.tags') }}:</label>
        <select class="form-select input-tag" id="tags" name="tags[]" multiple required>
            <option disabled value="">{{ __('blogs.tags') }}...</option>
            @foreach ($inputTags as $tag)
                <option value="{{ $tag }}" selected>{{ $tag }}</option>
            @endforeach
        </select>
        <div class="invalid-feedback">{{ textError('tags') }}</div>
    </div>

    @if (isAdmin())
        @php
            $isDelayed = old('delay', $article->published_at > now());
        @endphp
        <div class="form-check mb-3">
            <input type="checkbox" class="form-check-input" value="1" name="delay" id="delay" onchange="showDelayForm(this);" @checked($isDelayed)>
            <label for="delay" class="form-check-label">{{ __('blogs.delay_publication') }}</label>
        </div>

        <div class="col-sm-6 col-md-4 mb-3 js-published" @style(['display: none' => !$isDelayed])>
            <label for="published" class="form-label">{{ __('blogs.date_publication') }}</label>
            <input class="form-control<?= hasError('published') ?>" type="datetime-local" name="published" id="published" value="{{ old('published', date('Y-m-d\TH:i', $article->published_at?->timestamp)) }}">
            <div class="invalid-feedback">{{ textError('published') }}</div>
        </div>
    @endif

    @include('app/_upload_image', [
        'model' => $article,
        'paste' => true,
    ])

    <button class="btn btn-primary">
        {{ $article->exists ? __('main.change') : __('main.publish') }}
    </button>

    @if (! $article->exists)
        <button type="submit" class="btn btn-secondary" name="action" value="draft">
            {{ __('blogs.add_draft') }}
        </button>
    @elseif ($article->draft)
        <button type="submit" class="btn btn-success" name="action" value="publish">
            {{ __('main.publish') }}
        </button>
    @endif
</form>

@push('scripts')
    <script>
        function showDelayForm(el) {
            $('.js-published').toggle($(el).is(':checked'));
            return false;
        }
    </script>
@endpush
