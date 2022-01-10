@section('header')
    <h1>{{ __('settings.files') }}</h1>
@stop

<form method="post">
    @csrf
    <div class="mb-3{{ hasError('sets[filesize]') }}">
        <label for="filesize" class="form-label">{{ __('main.max_file_weight') }} (Mb):</label>
        <input type="number" class="form-control" id="filesize" name="sets[filesize]" maxlength="3" value="{{ getInput('sets.filesize', round($settings['filesize'] / 1048576)) }}" required>
        <div class="invalid-feedback">{{ textError('sets[filesize]') }}</div>

        <input type="hidden" value="1048576" name="mods[filesize]">
        <span class="text-muted fst-italic">{{ __('main.server_limit') }}: {{ ini_get('upload_max_filesize') }}</span>
    </div>

    <div class="mb-3{{ hasError('sets[maxfiles]') }}">
        <label for="maxfiles" class="form-label">{{ __('settings.loads_max_files') }}:</label>
        <input type="number" class="form-control" id="maxfiles" name="sets[maxfiles]" maxlength="2" value="{{ getInput('sets.maxfiles', $settings['maxfiles']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[maxfiles]') }}</div>
    </div>

    <div class="mb-3{{ hasError('sets[file_extensions]') }}">
        <label for="file_extensions" class="form-label">{{ __('main.valid_file_extensions') }}:</label>
        <textarea class="form-control" id="file_extensions" name="sets[file_extensions]" required>{{ getInput('sets.file_extensions', $settings['file_extensions']) }}</textarea>
        <div class="invalid-feedback">{{ textError('sets[file_extensions]') }}</div>
    </div>

    <div class="mb-3{{ hasError('sets[screensize]') }}">
        <label for="screensize" class="form-label">{{ __('settings.images_reduction_size') }} (px):</label>
        <input type="number" class="form-control" id="screensize" name="sets[screensize]" maxlength="4" value="{{ getInput('sets.screensize', $settings['screensize']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[screensize]') }}</div>
    </div>

    <div class="mb-3{{ hasError('sets[previewsize]') }}">
        <label for="previewsize" class="form-label">{{ __('settings.images_preview_size') }} (px):</label>
        <input type="number" class="form-control" id="previewsize" name="sets[previewsize]" maxlength="3" value="{{ getInput('sets.previewsize', $settings['previewsize']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[previewsize]') }}</div>
    </div>

    <div class="form-check mb-3">
        <input type="hidden" value="0" name="sets[copyfoto]">
        <input type="checkbox" class="form-check-input" value="1" name="sets[copyfoto]" id="copyfoto"{{ getInput('sets.copyfoto', $settings['copyfoto']) ? ' checked' : '' }}>
        <label class="form-check-label" for="copyfoto">{{ __('settings.images_copyright') }}</label>
    </div>

    <img src="/assets/img/images/watermark.png" alt="watermark" title="{{ config('app.url') }}/assets/img/images/watermark.png"><br>

    <p class="text-muted fst-italic">
        {{ __('settings.images_hint') }}
    </p>

    <div class="mb-3{{ hasError('sets[archive_file_path]') }}">
        <label for="archive_file_path" class="form-label">{{ __('settings.archive_file_path') }}:</label>
        <input type="hidden" name="opt[archive_file_path]" value="1">
        <input type="text" class="form-control" id="archive_file_path" name="sets[archive_file_path]" value="{{ getInput('sets.archive_file_path', $settings['archive_file_path']) }}">
        <div class="invalid-feedback">{{ textError('sets[archive_file_path]') }}</div>
        <p class="text-muted fst-italic">
            {{ __('settings.archive_file_path_hint') }}
        </p>
    </div>

    <button class="btn btn-primary">{{ __('main.save') }}</button>
</form>
