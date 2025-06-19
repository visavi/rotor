@section('header')
    <h1>{{ __('settings.seo') }}</h1>
@stop

<form method="post">
    @csrf
    @php
        $inputTemplate = old('sets.slug_template', $settings['slug_template']);
    @endphp

    <div class="mb-3">
        <label for="slug_template" class="form-label">{{ __('settings.seo_slug_template') }}:</label>
        <select class="form-select{{ hasError('sets[slug_template]') }}" id="slug_template" name="sets[slug_template]">
            @foreach ($slugs as $key => $template)
                <option value="{{ $key }}" @selected($key === $inputTemplate)>
                    {{ $template }}
                </option>
            @endforeach
        </select>
        <div class="invalid-feedback">{{ textError('sets[slug_template]') }}</div>
    </div>

    <button class="btn btn-primary">{{ __('main.save') }}</button>
</form>
