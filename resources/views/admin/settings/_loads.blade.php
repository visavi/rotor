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

    <div class="mb-3{{ hasError('sets[fileupload]') }}">
        <label for="fileupload" class="form-label">{{ __('main.max_file_weight') }} (Mb):</label>
        <input type="number" class="form-control" id="fileupload" name="sets[fileupload]" maxlength="3" value="{{ getInput('sets.fileupload', round($settings['fileupload'] / 1048576)) }}" required>
        <div class="invalid-feedback">{{ textError('sets[fileupload]') }}</div>

        <input type="hidden" value="1048576" name="mods[fileupload]">
        <span class="text-muted fst-italic">{{ __('main.server_limit') }}: {{ ini_get('upload_max_filesize') }}</span>
    </div>

    <div class="mb-3{{ hasError('sets[allowextload]') }}">
        <label for="allowextload" class="form-label">{{ __('main.valid_file_extensions') }}:</label>
        <textarea class="form-control" id="allowextload" name="sets[allowextload]" required>{{ getInput('sets.allowextload', $settings['allowextload']) }}</textarea>
        <div class="invalid-feedback">{{ textError('sets[allowextload]') }}</div>
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

    <button class="btn btn-primary">{{ __('main.save') }}</button>
</form>
