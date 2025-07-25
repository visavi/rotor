@section('header')
    <h1>{{ __('settings.loads') }}</h1>
@stop

<form method="post">
    @csrf
    <div class="mb-3{{ hasError('sets[downlist]') }}">
        <label for="downlist" class="form-label">{{ __('settings.loads_per_page') }}:</label>
        <input type="number" class="form-control" id="downlist" name="sets[downlist]" maxlength="2" value="{{ getInput('sets.downlist', $settings['downlist']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[downlist]') }}</div>
    </div>

    <div class="mb-3{{ hasError('sets[ziplist]') }}">
        <label for="ziplist" class="form-label">{{ __('settings.loads_archives') }}:</label>
        <input type="number" class="form-control" id="ziplist" name="sets[ziplist]" maxlength="2" value="{{ getInput('sets.ziplist', $settings['ziplist']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[ziplist]') }}</div>
    </div>

    <div class="form-check mb-3">
        <input type="hidden" value="0" name="sets[downupload]">
        <input type="checkbox" class="form-check-input" value="1" name="sets[downupload]" id="downupload"{{ getInput('sets.downupload', $settings['downupload']) ? ' checked' : '' }}>
        <label for="downupload" class="form-check-label">{{ __('settings.loads_files_allow') }}</label>
    </div>

    <div class="form-check mb-3">
        <input type="hidden" value="0" name="sets[down_guest_download]">
        <input type="checkbox" class="form-check-input" value="1" name="sets[down_guest_download]" id="down_guest_download"{{ getInput('sets.down_guest_download', $settings['down_guest_download']) ? ' checked' : '' }}>
        <label for="down_guest_download" class="form-check-label">{{ __('settings.loads_guests_download_allow') }}</label>
    </div>

    <div class="form-check mb-3">
        <input type="hidden" value="0" name="sets[down_allow_links]">
        <input type="checkbox" class="form-check-input" value="1" name="sets[down_allow_links]" id="down_allow_links"{{ getInput('sets.down_allow_links', $settings['down_allow_links']) ? ' checked' : '' }}>
        <label for="down_allow_links" class="form-check-label">{{ __('settings.down_allow_links') }}</label>
    </div>

    <div class="mb-3{{ hasError('sets[down_point]') }}">
        <label for="down_point" class="form-label">{{ __('settings.down_point') }}:</label>
        <input type="number" class="form-control" id="down_point" name="sets[down_point]" maxlength="2" value="{{ getInput('sets.down_point', $settings['down_point']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[down_point]') }}</div>
    </div>

    <div class="mb-3{{ hasError('sets[down_money]') }}">
        <label for="down_money" class="form-label">{{ __('settings.down_money') }}:</label>
        <input type="number" class="form-control" id="down_money" name="sets[down_money]" maxlength="2" value="{{ getInput('sets.down_money', $settings['down_money']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[down_money]') }}</div>
    </div>

    <div class="mb-3">
        <label for="down_title_min" class="form-label">{{ __('settings.loads_title_length') }}:</label>
        <div class="d-flex gap-2">
            <input type="number" class="form-control{{ hasError('sets[down_title_min]') }}" id="down_title_min" name="sets[down_title_min]" value="{{ old('sets.down_title_min', $settings['down_title_min']) }}" placeholder="{{ __('main.min') }}" required>
            <input type="number" class="form-control{{ hasError('sets[down_title_max]') }}" name="sets[down_title_max]" value="{{ old('sets.down_title_max', $settings['down_title_max']) }}" placeholder="{{ __('main.max') }}" required>
        </div>
        <div class="invalid-feedback d-block">
            <div>{{ textError('sets[down_title_min]') }}</div>
            <div>{{ textError('sets[down_title_max]') }}</div>
        </div>
    </div>

    <div class="mb-3">
        <label for="down_text_min" class="form-label">{{ __('settings.loads_text_length') }}:</label>
        <div class="d-flex gap-2">
            <input type="number" class="form-control{{ hasError('sets[down_text_min]') }}" id="down_text_min" name="sets[down_text_min]" value="{{ old('sets.down_text_min', $settings['down_text_min']) }}" placeholder="{{ __('main.min') }}" required>
            <input type="number" class="form-control{{ hasError('sets[down_text_max]') }}" name="sets[down_text_max]" value="{{ old('sets.down_text_max', $settings['down_text_max']) }}" placeholder="{{ __('main.max') }}" required>
        </div>
        <div class="invalid-feedback d-block">
            <div>{{ textError('sets[down_text_min]') }}</div>
            <div>{{ textError('sets[down_text_max]') }}</div>
        </div>
    </div>

    <div class="mb-3">
        <label for="down_link_min" class="form-label">{{ __('settings.loads_link_length') }}:</label>
        <div class="d-flex gap-2">
            <input type="number" class="form-control{{ hasError('sets[down_link_min]') }}" id="down_link_min" name="sets[down_link_min]" value="{{ old('sets.down_link_min', $settings['down_link_min']) }}" placeholder="{{ __('main.min') }}" required>
            <input type="number" class="form-control{{ hasError('sets[down_link_max]') }}" name="sets[down_link_max]" value="{{ old('sets.down_link_max', $settings['down_link_max']) }}" placeholder="{{ __('main.max') }}" required>
        </div>
        <div class="invalid-feedback d-block">
            <div>{{ textError('sets[down_link_min]') }}</div>
            <div>{{ textError('sets[down_link_max]') }}</div>
        </div>
    </div>

    <div class="mb-3">
        <label for="down_category_min" class="form-label">{{ __('settings.loads_category_length') }}:</label>
        <div class="d-flex gap-2">
            <input type="number" class="form-control{{ hasError('sets[down_category_min]') }}" id="down_category_min" name="sets[down_category_min]" value="{{ old('sets.down_category_min', $settings['down_category_min']) }}" placeholder="{{ __('main.min') }}" required>
            <input type="number" class="form-control{{ hasError('sets[down_category_max]') }}" name="sets[down_category_max]" value="{{ old('sets.down_category_max', $settings['down_category_max']) }}" placeholder="{{ __('main.max') }}" required>
        </div>
        <div class="invalid-feedback d-block">
            <div>{{ textError('sets[down_category_min]') }}</div>
            <div>{{ textError('sets[down_category_max]') }}</div>
        </div>
    </div>

    <button class="btn btn-primary">{{ __('main.save') }}</button>
</form>
