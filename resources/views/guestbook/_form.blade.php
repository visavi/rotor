@if (getUser())
    <div class="section-form mb-3 shadow">
        <form action="{{ route('guestbook.create') }}" method="post">
            @csrf
            <div class="mb-3{{ hasError('msg') }}">
                <label for="msg" class="form-label">{{ __('main.message') }}:</label>
                <textarea class="form-control markItUp" maxlength="{{ setting('guestbook_text_max') }}" id="msg" rows="5" name="msg" placeholder="{{ __('main.message') }}" required>{{ getInput('msg') }}</textarea>
                <div class="invalid-feedback">{{ textError('msg') }}</div>
                <span class="js-textarea-counter"></span>
            </div>

            @include('app/_upload_file', [
                'model' => App\Models\Guestbook::getModel(),
                'files' => $files,
            ])

            <button class="btn btn-primary">{{ __('main.write') }}</button>
        </form>
    </div>

@elseif (setting('bookadds'))
    <div class="section-form mb-3 shadow">
        <form action="{{ route('guestbook.create') }}" method="post">
            @csrf
            <div class="mb-3{{ hasError('guest_name') }}">
                <label for="inputName" class="form-label">{{ __('users.name') }}:</label>
                <input class="form-control" id="inputName" name="guest_name" maxlength="20" value="{{ getInput('guest_name') }}">
                <div class="invalid-feedback">{{ textError('guest_name') }}</div>
            </div>

            <div class="mb-3{{ hasError('msg') }}">
                <label for="msg" class="form-label">{{ __('main.message') }}:</label>
                <textarea class="form-control" id="msg" rows="5" maxlength="{{ setting('guestbook_text_max') }}" name="msg" placeholder="{{ __('main.message') }}" required>{{ getInput('msg') }}</textarea>
                <div class="invalid-feedback">{{ textError('msg') }}</div>
            </div>

            {{ getCaptcha() }}
            <button class="btn btn-primary">{{ __('main.write') }}</button>
        </form>
    </div>
@else
    {{ showError(__('main.not_authorized')) }}
@endif
